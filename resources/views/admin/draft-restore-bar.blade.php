<div x-show="hasDraft"
     x-cloak
     x-transition
     style="background: #FEF3CD; border: 1px solid #F9D77E; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; gap: 12px; color: #8A6D3B; font-size: 0.88rem;">
    <div style="display: flex; align-items: center; gap: 8px;">
        <span style="font-size: 1.1rem;">⚠️</span>
        <span>You have unsaved text from earlier. Restore it?</span>
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="button" @click="restoreDraft()" class="btn-primary" style="padding: 6px 14px; font-size: 0.8rem; width: auto; margin-top: 0; background: #8A6D3B; border-color: #8A6D3B; cursor: pointer;">
            Restore
        </button>
        <button type="button" @click="discardDraft()" class="btn-ghost" style="padding: 6px 14px; font-size: 0.8rem; border-color: #D9BE7D; color: #8A6D3B; cursor: pointer;">
            Discard
        </button>
    </div>
</div>

