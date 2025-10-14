
<!DOCTYPE html>
<html lang="en" class="light-style customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('assets/') }}/">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - Chicking BJM</title>
  <meta name="csrf-token" content="{{ csrf_token() }}"> <!-- Add CSRF meta -->
  
  <!-- Icons -->
  <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

  <!-- Core CSS -->
  <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

  <!-- Page CSS -->
  <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}" />
</head>

<body>
  <div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
      <div class="authentication-inner">
        <div class="card">
          <div class="card-body">
            <!-- Logo -->
            <div class="app-brand justify-content-center mb-4">
              <a href="#" class="app-brand-link gap-2">
                <span class="app-brand-text demo text-body fw-bolder">Chicking BJM</span>
              </a>
            </div>
            
            <h4 class="mb-2">Selamat Datang! 👋</h4>
            <p class="mb-4">Silakan login untuk melanjutkan</p>

            <!-- Messages -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              {{ session('error') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <!-- LOGIN FORM -->
            <form id="loginForm" action="{{ route('login') }}" method="POST">
              @csrf <!-- CSRF Token -->
              
              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control @error('username') is-invalid @enderror" 
                       id="username" name="username" value="{{ old('username') }}" 
                       placeholder="Masukkan username" autofocus required />
                @error('username')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="mb-3 form-password-toggle">
                <label class="form-label" for="password">Password</label>
                <div class="input-group input-group-merge">
                  <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                         name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" required />
                  <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                </div>
                @error('password')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="mb-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="remember" name="remember" />
                  <label class="form-check-label" for="remember">Ingat saya</label>
                </div>
              </div>

              <div class="mb-3">
                <button class="btn btn-primary d-grid w-100" type="submit">
                  <span class="d-flex align-items-center justify-content-center">
                    <i class="bx bx-log-in me-2"></i>
                    Masuk
                  </span>
                </button>
              </div>
            </form>

            @if(app()->environment(['local', 'staging']))
            <div class="divider my-4">
              <div class="divider-text">Quick Login (Dev)</div>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-center">
              <a href="{{ route('quick-login', ['as' => 'admin']) }}" class="btn btn-outline-success btn-sm">
                <i class="bx bx-user-check me-1"></i>
                Admin
              </a>
              <a href="{{ route('quick-login', ['as' => 'manager.inventory']) }}" class="btn btn-outline-warning btn-sm">
                <i class="bx bx-user me-1"></i>
                Manager
              </a>
              <a href="{{ route('quick-login', ['as' => 'kasir1']) }}" class="btn btn-outline-info btn-sm">
                <i class="bx bx-user me-1"></i>
                Staff
              </a>
            </div>
            @endif

            <!-- Debug Info (hanya untuk development) -->
            @if(app()->environment('local'))
            <div class="mt-4 small text-muted">
              <strong>Debug Info:</strong><br>
              - CSRF Token: {{ csrf_token() }}<br>
              - Session ID: {{ session()->getId() }}<br>
              - Login Route: {{ route('login') }}
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Core JS -->
  <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
  <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
  <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
  <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
  <script src="{{ asset('assets/js/main.js') }}"></script>

  <script>
    // Set CSRF token globally untuk AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Toggle password visibility
    document.querySelector('.form-password-toggle .input-group-text').addEventListener('click', function() {
      const passwordInput = document.getElementById('password');
      const icon = this.querySelector('i');
      
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('bx-hide');
        icon.classList.add('bx-show');
      } else {
        passwordInput.type = 'password';
        icon.classList.remove('bx-show');
        icon.classList.add('bx-hide');
      }
    });

    // Form submission debug
    document.getElementById('loginForm').addEventListener('submit', function(e) {
      console.log('Form submitted with CSRF token:', document.querySelector('input[name="_token"]').value);
    });
  </script>
</body>
</html>