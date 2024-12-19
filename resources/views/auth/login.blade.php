<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Support|CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
.gradient-custom-2 {
/* fallback for old browsers */
background: #fccb90;

/* Chrome 10-25, Safari 5.1-6 */
background: -webkit-linear-gradient(to right, #ee7724, #d8363a, #dd3675, #b44593);

/* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
background: linear-gradient(to right, #ee7724, #d8363a, #dd3675, #b44593);
}
.logo-image {
    display: block;
    margin-left: auto;
    margin-right: auto;
    height:100px;
    width:100px;
}

@media (min-width: 768px) {
.gradient-form {
height: 100vh !important;
}
}
@media (min-width: 769px) {
.gradient-custom-2 {
border-top-right-radius: .3rem;
border-bottom-right-radius: .3rem;
}
}
    </style>  
</head>
  <body>
  <section class="vh-100" >
  <div class="container py-5 h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col col-xl-10">
        <div class="card" style="border-radius: 1rem;">
          <div class="row g-0">
            <div class="col-md-6 col-lg-5 d-none d-md-block">
              <img src="https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-login-form/img1.webp"
                alt="login form" class="img-fluid" style="border-radius: 1rem 0 0 1rem;" />
            </div>
            <div class="col-md-6 col-lg-7 d-flex align-items-center">
              <div class="card-body p-4 p-lg-5 text-black">
    <!-- Replace the Laravel Blade components with standard HTML elements -->

    <form method="POST" action="{{ route('login') }}">
        @csrf
        @if ($errors->has('access_denied'))
    <div class="alert alert-danger">
        {{ $errors->first('access_denied') }}
    </div>
@endif

@if ($errors->has('login_failed'))
    <div class="alert alert-danger">
        {{ $errors->first('login_failed') }}
    </div>
@endif
<?php if (session('logged_out')): ?>
    <script>
        // Prevent back navigation
        window.history.pushState(null, null, window.location.href);
        window.onpopstate = function () {
            window.history.go(1);
        };
    </script>
    <?php session()->forget('logged_out'); ?>
<?php endif; ?>
        <!-- Header and Logo -->
        <div class="row mb-4">
    <div class="text-center mb-3 pb-1">
        <img src="{{ asset('assets/img/Logo.png') }}" alt="Logo" class="logo-image">
        <h5 class="fw-normal" style="letter-spacing: 1px;">Sign into your account</h5>
    </div>
</div>

        <!-- Email Address -->
        <div class="row mb-4">
            <label class="form-label" for="email">Email address</label>
            <input type="email" id="email" name="email" class="form-control form-control-lg shadow-sm" value="{{ old('email') }}" required autofocus autocomplete="username" />
            @error('email')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <!-- Password -->
        <div class="row mb-4">
            <label class="form-label" for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control form-control-lg shadow-sm" required autocomplete="current-password" />
            @error('password')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="row mb-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="row mb-4">
            <button class="btn btn-dark btn-lg btn-block" type="submit">
                Login
            </button>
        </div>

        <!-- Forgot Password Link -->
        <div class="row mb-4">
            @if (Route::has('password.request'))
                <a class="small text-muted" href="#">
                    Forgot your password?
                </a>
            @endif
        </div>
    </form>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script>
        // Prevent going back to the previous page after login
        window.history.pushState(null, null, window.location.href);
        window.onpopstate = function () {
            window.history.go(1);
        };

        // Ensure user can't navigate back to the login page
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        })
        setInterval(function() {
    location.reload();
}, 600000);
    </script> 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
     
</body>
</html>