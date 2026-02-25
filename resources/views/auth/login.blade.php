<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SIMANIS | Login</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Google Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .login-wrapper {
            min-height: 100vh;
        }

        .login-left {
            max-width: 420px;
            width: 100%;
        }

        .login-logo {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #eef2ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #4f46e5;
            font-size: 20px;
            margin-bottom: 24px;
        }

        .login-right {
            background-image: url("images/Badan-Pusat-Statistik.jpeg");
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .login-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.35);
        }

        .login-right-content {
            position: relative;
            z-index: 2;
            color: #fff;
        }

        .form-control::placeholder {
            font-weight: 400 !important;
            opacity: 0.55 !important;
            font-size: 0.95rem !important;
        }

        /* Chrome/Safari */
        .form-control::-webkit-input-placeholder {
            font-weight: 400 !important;
            opacity: 0.55 !important;
            font-size: 0.95rem !important;
        }

        /* Firefox */
        .form-control::-moz-placeholder {
            font-weight: 400 !important;
            opacity: 0.55 !important;
            font-size: 0.95rem !important;
        }

        .input-group-text i {
            color: #6c757d;
        }

        .input-group .btn {
            border-left: 0;
        }

        .input-group .form-control:focus,
        .input-group .btn:focus {
            box-shadow: none;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row login-wrapper">

        {{-- LEFT : LOGIN FORM --}}
        <div class="col-lg-4 d-flex align-items-center justify-content-center bg-white">
            <div class="login-left px-4">
                <h4 class="fw-semibold mb-2">Sistem Manajemen Inventaris di BPS Provinsi DKI Jakarta</h4>
                <p class="text-muted mb-4">
                    Silakan login untuk mengakses sistem
                </p>

                @if($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   value="{{ old('email') }}"
                                   placeholder="contoh@email.com"
                                   required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-lock"></i>
                            </span>

                            <input id="password"
                                   type="password"
                                   name="password"
                                   class="form-control"
                                   placeholder="Masukkan password"
                                   required>
                            <span class="input-group-text bg-white">
                                <button class="btn"
                                    type="button"
                                    id="togglePassword"
                                    aria-label="Tampilkan password">
                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                            </button>
                            </span>
                            
                        </div>
                    </div>

                    <button class="btn btn-primary w-100">
                        Login
                    </button>
                </form>

                <div class="text-center mt-4">
                    <small class="text-muted">
                        Copyright ©BPS Provinsi DKI Jakarta
                    </small>
                </div>

            </div>
        </div>

        {{-- RIGHT : IMAGE --}}
        <div class="col-lg-8 d-none d-lg-block login-right">
            <div class="login-overlay"></div>

            <div class="h-100 d-flex align-items-end p-5">
                <div class="login-right-content">
                    <h1 class="fw-semibold mb-2">Selamat Datang!</h1>
                    <p class="mb-4">di Sistem Manajemen Inventaris</p>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const passwordInput = document.getElementById('password');
    const toggleBtn = document.getElementById('togglePassword');
    const toggleIcon = document.getElementById('togglePasswordIcon');

    toggleBtn.addEventListener('click', () => {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';

        toggleIcon.classList.toggle('bi-eye', !isPassword);
        toggleIcon.classList.toggle('bi-eye-slash', isPassword);
    });
</script>

</body>
</html>
