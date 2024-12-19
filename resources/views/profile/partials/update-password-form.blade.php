<section>
    <header>
        <h2 class="text-lg font-medium text-dark">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-muted">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-4">
        @csrf
        @method('put')
<div class="row">
<!-- Current Password -->
<div class="col">
            <label for="update_password_current_password" class="form-label">{{ __('Current Password') }}</label>
            <input type="password" class="form-control" id="update_password_current_password" name="current_password" autocomplete="current-password">
            @if($errors->updatePassword->get('current_password'))
                <div class="text-danger mt-2">
                    {{ $errors->first('current_password') }}
                </div>
            @endif
        </div>

        <!-- New Password -->
        <div class="col">
            <label for="update_password_password" class="form-label">{{ __('New Password') }}</label>
            <input type="password" class="form-control" id="update_password_password" name="password" autocomplete="new-password">
            @if($errors->updatePassword->get('password'))
                <div class="text-danger mt-2">
                    {{ $errors->first('password') }}
                </div>
            @endif
        </div>

        <!-- Confirm Password -->
        <div class="col">
            <label for="update_password_password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
            <input type="password" class="form-control" id="update_password_password_confirmation" name="password_confirmation" autocomplete="new-password">
            @if($errors->updatePassword->get('password_confirmation'))
                <div class="text-danger mt-2">
                    {{ $errors->first('password_confirmation') }}
                </div>
            @endif
        </div>

</div>
        
        <!-- Save Button -->
        <div class="d-flex align-items-center gap-4 mt-3">
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2000)" class="text-success">
                    {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>
