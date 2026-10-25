<x-layouts.admin title="Change Password">
    <div class="card" style="max-width: 440px">
        @if (session('status'))
            <div class="alert alert-ok" style="margin-bottom: 20px;">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.password.update') }}">
            @csrf
            @method('PUT')

            <div class="field" style="margin-top: 0;">
                <label for="current_password">Current password</label>
                <input id="current_password" name="current_password" type="password"
                       class="input @error('current_password') is-invalid @enderror"
                       autocomplete="current-password" required autofocus>
                @error('current_password') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label for="password">New password</label>
                <input id="password" name="password" type="password"
                       class="input @error('password') is-invalid @enderror"
                       autocomplete="new-password" required>
                @error('password') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">Confirm new password</label>
                <input id="password_confirmation" name="password_confirmation" type="password"
                       class="input" autocomplete="new-password" required>
            </div>

            <button type="submit" class="btn-primary">Update password</button>
        </form>
    </div>
</x-layouts.admin>