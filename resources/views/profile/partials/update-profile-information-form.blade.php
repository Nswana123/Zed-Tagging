<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6">
        @csrf
        @method('patch')
<div class="row">
   <!-- First Name -->
   <div class="col">
            <label for="fname" class="form-label">{{ __('First Name') }}</label>
            <input type="text" class="form-control" id="fname" name="fname" value="{{ old('fname', $user->fname) }}" required autofocus autocomplete="fname">
            @if($errors->get('fname'))
                <div class="text-danger mt-2">
                    {{ $errors->first('fname') }}
                </div>
            @endif
        </div>

        <!-- Last Name -->
        <div class="col">
            <label for="lname" class="form-label">{{ __('Last Name') }}</label>
            <input type="text" class="form-control" id="lname" name="lname" value="{{ old('lname', $user->lname) }}" required autofocus autocomplete="lname">
            @if($errors->get('lname'))
                <div class="text-danger mt-2">
                    {{ $errors->first('lname') }}
                </div>
            @endif
        </div>
        </div>
        <div class="row mt-3">
        <div class="col">
            <label for="lname" class="form-label">{{ __('Contact Number') }}</label>
            <input type="text" class="form-control" id="mobile" name="mobile" value="{{ old('mobile', $user->mobile) }}" required autofocus autocomplete="mobile">
            @if($errors->get('mobile'))
                <div class="text-danger mt-2">
                    {{ $errors->first('mobile') }}
                </div>
            @endif
        </div>
             <!-- Email -->
        <div class="col">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
            @if($errors->get('email'))
                <div class="text-danger mt-2">
                    {{ $errors->first('email') }}
                </div>
            @endif

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-sm text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="btn btn-link p-0 text-decoration-underline">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-success">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div> 
        </div>
        

        <!-- Save Button -->
        <div class="d-flex align-items-center gap-4 mt-3">
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2000)" class="text-success">
                    {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>
