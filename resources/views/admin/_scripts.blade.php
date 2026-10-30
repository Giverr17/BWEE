<script>
window.BWEE_TRANSLATE_URL = "{{ route('admin.translate') }}";

window.adminForm = function (config = {}) {
    return {
        formKey: config.formKey || 'form',
        recordId: config.recordId || null,
        pairs: config.pairs || [],
        autoOn: config.autoOn !== undefined ? config.autoOn : true,
        touched: config.touched || {},
        translateStatus: '',
        translateNote: '',
        hasDraft: false,
        draftTimestamp: null,
        isDirty: false,
        currentRequestId: 0,
        activeController: null,
        debounceTimer: null,
        saveDraftTimer: null,

        get draftKey() {
            const id = this.recordId ? String(this.recordId) : 'new';
            return `bwee_draft_${this.formKey}_${id}`;
        },

        init() {
            this.checkDraft();

            if (this.$watch) {
                this.$watch('recordId', () => {
                    this.checkDraft();
                });
            }

            this.$el.addEventListener('input', (e) => {
                this.isDirty = true;
                this.handleInput(e);
                this.queueSaveDraft();
            });

            window.addEventListener('notify', () => this.clearDraft());
            window.addEventListener('toast', (e) => {
                if (e.detail?.type === 'success') {
                    this.clearDraft();
                }
            });

            window.addEventListener('beforeunload', (e) => {
                if (this.isDirty) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        },

        getModelName(el) {
            if (!el || !el.attributes) return '';
            if (el.getAttribute('data-field')) return el.getAttribute('data-field');
            if (el.getAttribute('name')) return el.getAttribute('name');
            for (let i = 0; i < el.attributes.length; i++) {
                const attr = el.attributes[i];
                if (attr.name.startsWith('wire:model')) {
                    return attr.value;
                }
            }
            return '';
        },

        findInputByModel(modelName) {
            if (!modelName) return null;
            let el = this.$el.querySelector(`[name="${modelName}"], [data-field="${modelName}"]`);
            if (el) return el;
            const all = this.$el.querySelectorAll('input, textarea, select');
            for (let i = 0; i < all.length; i++) {
                const input = all[i];
                for (let j = 0; j < input.attributes.length; j++) {
                    const attr = input.attributes[j];
                    if (attr.name.startsWith('wire:model') && attr.value === modelName) {
                        return input;
                    }
                }
            }
            return null;
        },

        checkDraft() {
            try {
                const raw = localStorage.getItem(this.draftKey);
                if (!raw) {
                    this.hasDraft = false;
                    return;
                }
                const parsed = JSON.parse(raw);
                if (parsed && parsed.data && Object.keys(parsed.data).length > 0) {
                    this.hasDraft = true;
                    this.draftTimestamp = parsed.timestamp;
                } else {
                    this.hasDraft = false;
                }
            } catch (err) {
                this.hasDraft = false;
            }
        },

        queueSaveDraft() {
            clearTimeout(this.saveDraftTimer);
            this.saveDraftTimer = setTimeout(() => {
                this.saveDraft();
            }, 500);
        },

        saveDraft() {
            try {
                const data = {};
                const inputs = this.$el.querySelectorAll('input:not([type="password"]):not([type="file"]):not([type="hidden"]), textarea, select');
                inputs.forEach(input => {
                    const name = this.getModelName(input);
                    if (name) {
                        data[name] = input.value;
                    }
                });

                if (Object.keys(data).length > 0) {
                    localStorage.setItem(this.draftKey, JSON.stringify({
                        data,
                        timestamp: Date.now()
                    }));
                }
            } catch (err) {}
        },

        restoreDraft() {
            try {
                const raw = localStorage.getItem(this.draftKey);
                if (!raw) return;
                const parsed = JSON.parse(raw);
                if (!parsed || !parsed.data) return;

                const data = parsed.data;
                for (const [key, val] of Object.entries(data)) {
                    const input = this.findInputByModel(key);
                    if (input) {
                        input.value = val;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    }

                    if (window.Livewire && this.$wire) {
                        try {
                            this.$wire.set(key, val);
                        } catch (e) {}
                    }

                    if (key.endsWith('_fr') || key.includes('_fr')) {
                        const base = key.replace('_fr', '');
                        this.touched[base] = Boolean(val && val.trim());
                    }
                }

                this.hasDraft = false;
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'info', message: 'Unsaved draft restored.' }
                }));
            } catch (err) {
                this.hasDraft = false;
            }
        },

        discardDraft() {
            try {
                localStorage.removeItem(this.draftKey);
            } catch (err) {}
            this.hasDraft = false;
        },

        clearDraft() {
            try {
                localStorage.removeItem(this.draftKey);
            } catch (err) {}
            this.hasDraft = false;
            this.isDirty = false;
        },

        handleInput(e) {
            const target = e.target;
            const name = this.getModelName(target);
            if (!name) return;

            if (name.endsWith('_fr')) {
                const base = name.replace('_fr', '');
                this.touched[base] = Boolean(target.value && target.value.trim());
                return;
            }

            let matchedPair = null;
            if (this.pairs && this.pairs.length > 0) {
                matchedPair = this.pairs.find(p => p.en === name || (p.en && name.includes(p.en)));
            }
            if (!matchedPair && name.endsWith('_en')) {
                const base = name.replace('_en', '');
                matchedPair = { en: name, fr: `${base}_fr`, base };
            }

            if (matchedPair) {
                this.queueTranslate(matchedPair, target.value);
            }
        },

        queueTranslate(pair, englishText) {
            if (!this.autoOn) return;
            if (this.touched[pair.base]) return;

            const text = (englishText || '').trim();
            if (text.length < 3) return;

            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                this.executeTranslate(pair, text);
            }, 400);
        },

        executeTranslate(pair, englishText) {
            if (!this.autoOn || this.touched[pair.base]) return;

            if (this.activeController) {
                this.activeController.abort();
            }
            this.activeController = new AbortController();
            const reqId = ++this.currentRequestId;

            this.translateStatus = 'translating';
            this.translateNote = '';

            const timeoutId = setTimeout(() => {
                if (this.activeController) {
                    this.activeController.abort();
                }
            }, 8000);

            const url = window.BWEE_TRANSLATE_URL || '/switch/translate';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    fields: {
                        [pair.base]: englishText
                    }
                }),
                signal: this.activeController.signal
            })
            .then(res => {
                clearTimeout(timeoutId);
                return res.json();
            })
            .then(data => {
                if (reqId !== this.currentRequestId) return;

                if (data.reason === 'rate_limited') {
                    this.translateStatus = 'rate_limited';
                    this.translateNote = 'Too many translations, try again in a moment';
                    return;
                }

                const translations = data.translations || data.translated;
                if (!translations || !translations[pair.base]) {
                    this.translateStatus = 'unavailable';
                    this.translateNote = 'Auto translation unavailable. You can type the French yourself.';
                    return;
                }

                if (this.touched[pair.base]) {
                    this.translateStatus = '';
                    this.translateNote = '';
                    return;
                }

                const frenchText = translations[pair.base];
                this.translateStatus = '';
                this.translateNote = '';

                const frInput = this.findInputByModel(pair.fr);
                if (frInput) {
                    frInput.value = frenchText;
                    frInput.dispatchEvent(new Event('input', { bubbles: true }));
                }

                if (window.Livewire && this.$wire) {
                    try {
                        this.$wire.set(pair.fr, frenchText);
                    } catch (e) {}
                }
            })
            .catch(err => {
                clearTimeout(timeoutId);
                if (err.name === 'AbortError') return;
                if (reqId !== this.currentRequestId) return;

                this.translateStatus = 'unavailable';
                this.translateNote = 'Auto translation unavailable. You can type the French yourself.';
            });
        }
    };
};

document.addEventListener('livewire:init', () => {
    if (window.Livewire && window.Livewire.hook) {
        window.Livewire.hook('request', ({ fail }) => {
            fail(({ status, preventDefault }) => {
                if (typeof preventDefault === 'function') {
                    preventDefault();
                }
                if (status === 419) {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            type: 'error',
                            message: 'Session expired. Please log in again in a new tab, then restore your draft.'
                        }
                    }));
                } else {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            type: 'error',
                            message: 'Connection problem. Your text is kept on this page and has been saved as a draft.'
                        }
                    }));
                }
            });
        });
    }
});
</script>
