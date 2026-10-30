{{-- Interactive Event Details Modal (Alpine.js) --}}
<div x-data="{
    open: false,
    event: null,
    init() {
        window.openEventModal = (data) => this.openModal(data);
    },
    openModal(data) {
        this.event = data;
        this.open = true;
        document.body.style.overflow = 'hidden';
    },
    closeModal() {
        this.open = false;
        document.body.style.overflow = '';
    }
}"
@open-event-modal.window="openModal($event.detail)"
@keydown.window.escape="closeModal()">

    <template x-teleport="body">
        <div x-cloak
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="position:fixed;inset:0;z-index:9999;background:rgba(18,11,38,0.85);backdrop-filter:blur(6px);display:flex;align-items:center;justify-content:center;padding:16px;overflow-y:auto"
             @click.self="closeModal()"
             role="dialog"
             aria-modal="true">

            <div style="background:var(--paper, #2B1660);border:1px solid var(--line);border-radius:18px;width:100%;max-width:720px;max-height:90vh;overflow-y:auto;box-shadow:0 24px 60px rgba(0,0,0,0.45);position:relative;margin:auto"
                 @click.stop>

                {{-- Close Button --}}
                <button @click="closeModal()"
                        type="button"
                        aria-label="{{ __('events.close') }}"
                        style="position:absolute;top:16px;right:16px;width:36px;height:36px;border-radius:50%;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);border:1px solid rgba(255,255,255,0.2);color:#fff;font-size:1.2rem;display:flex;align-items:center;justify-content:center;cursor:pointer;z-index:10;transition:all .15s">
                    &times;
                </button>

                {{-- Event Thumbnail Banner --}}
                <template x-if="event && event.thumbnail_url">
                    <div style="width:100%;height:220px;overflow:hidden;border-top-left-radius:17px;border-top-right-radius:17px;position:relative">
                        <img :src="event.thumbnail_url" :alt="event ? event.title : ''" style="width:100%;height:100%;object-fit:contain">
                    </div>
                </template>

                <div style="padding:28px 32px">
                    {{-- Tag, Date & Location Meta --}}
                    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;margin-bottom:12px">
                        <template x-if="event && event.tag">
                            <span style="background:var(--marigold-soft, #E8C97A);color:#331A52;font-size:0.75rem;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;padding:4px 10px;border-radius:99px" x-text="event.tag"></span>
                        </template>
                        <span style="color:var(--ink-soft);font-size:0.86rem;font-weight:500" x-show="event && event.date_label" x-text="event ? event.date_label : ''"></span>
                        <span style="color:var(--ink-soft);font-size:0.86rem;" x-show="event && event.date_label && event.location">&bull;</span>
                        <span style="color:var(--ink-soft);font-size:0.86rem;font-weight:500" x-show="event && event.location" x-text="event ? event.location : ''"></span>
                    </div>

                    {{-- Title --}}
                    <h2 style="font-family:'Fraunces',serif;font-size:1.65rem;line-height:1.25;color:var(--on-dark, #FBF7F1);margin-bottom:16px" x-text="event ? event.title : ''"></h2>

                    {{-- Description --}}
                    <div style="color:var(--ink);font-size:0.95rem;line-height:1.65;white-space:pre-line;margin-bottom:24px" x-text="event ? event.description : ''"></div>

                    {{-- Featured Speakers Section --}}
                    <template x-if="event && event.speakers && event.speakers.length > 0">
                        <div style="border-top:1px solid var(--line);padding-top:22px;margin-top:20px">
                            <h4 style="font-family:'Fraunces',serif;font-size:1.2rem;color:var(--on-dark, #FBF7F1);margin-bottom:14px;display:flex;align-items:center;gap:8px">
                                <span>{{ __('events.speakers_heading') }}</span>
                                <span style="font-size:0.75rem;font-family:'Instrument Sans',sans-serif;color:var(--ink-soft);font-weight:500" x-text="'(' + event.speakers.length + ')'"></span>
                            </h4>

                            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px">
                                <template x-for="(speaker, sIdx) in event.speakers" :key="sIdx">
                                    <div style="display:flex;align-items:center;gap:14px;background:var(--paper-dim, #331d6e);border:1px solid var(--line);border-radius:12px;padding:12px 14px">
                                        {{-- Photo or Initials Avatar --}}
                                        <template x-if="speaker.image_url">
                                            <img :src="speaker.image_url" :alt="speaker.name" style="width:52px;height:52px;border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid #C9962C;box-shadow:0 2px 6px rgba(0,0,0,0.12)">
                                        </template>
                                        <template x-if="!speaker.image_url">
                                            <div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg, #E8C97A, #C9962C);color:#1C1B1F;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.15rem;flex-shrink:0;box-shadow:0 2px 6px rgba(0,0,0,0.12)"
                                                 x-text="speaker.initials || (speaker.name ? speaker.name.charAt(0).toUpperCase() : '?')">
                                            </div>
                                        </template>

                                        <div style="flex:1;min-width:0">
                                            <div style="font-weight:700;font-size:0.95rem;color:var(--on-dark, #FBF7F1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis" x-text="speaker.name"></div>
                                            <div style="font-size:0.82rem;color:var(--clay, #14C3B5);margin-top:2px;line-height:1.3" x-text="speaker.title"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Modal Footer Actions --}}
                    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-top:28px;padding-top:20px;border-top:1px solid var(--line)">
                        <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center">
                            <template x-if="event && event.is_registration_open && event.register_url">
                                <a :href="event.register_url" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:6px;font-size:0.88rem;padding:8px 16px">
                                    <span>{{ __('events.register') }}</span>
                                    <span>&rarr;</span>
                                </a>
                            </template>
                            <template x-if="event && event.video_url">
                                <a :href="event.video_url" target="_blank" rel="noopener" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:6px;font-size:0.88rem;padding:8px 16px">
                                    <span>&#9658;</span>
                                    <span>{{ __('events.watch_session') }}</span>
                                </a>
                            </template>
                            <template x-if="event && event.cta_url">
                                <a :href="event.cta_url" target="_blank" rel="noopener" class="btn btn-secondary" style="font-size:0.88rem;padding:8px 16px" x-text="(event.cta_text || '{{ __('events.learn_more') }}') + ' &rarr;'"></a>
                            </template>
                        </div>

                        <button type="button" @click="closeModal()" class="btn btn-ghost" style="font-size:0.88rem;padding:8px 16px">
                            {{ __('events.close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
