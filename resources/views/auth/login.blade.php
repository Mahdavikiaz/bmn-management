<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMANIS | Login</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Google Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            min-height: 100vh;
            background:
                linear-gradient(rgba(255,255,255,0.15), rgba(255,255,255,0.15)),
                url("{{ asset('images/Badan-Pusat-Statistik.jpeg') }}") center center / cover no-repeat;
            overflow: hidden;
        }
         /* body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(180deg, #bfe3ff 0%, #eaf6ff 45%, #f8fbff 100%);
            overflow: hidden;
        } */

        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
        }

        .login-page::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(
                180deg,
                rgba(120, 190, 255, 0.18) 0%,
                rgba(255, 255, 255, 0.10) 45%,
                rgba(255, 255, 255, 0.22) 100%
            );
            backdrop-filter: blur(2px);
        }

        .login-card {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 430px;
            background: rgba(255, 255, 255, 0.78);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.45);
            border-radius: 24px;
            padding: 32px 28px 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.12);
        }

        .login-logo {
            width: 64px;
            height: 64px;
            margin: 0 auto 18px;
            border-radius: 18px;
            background: rgba(255,255,255,0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(0,0,0,.08);
        }

        .login-logo img {
            width: 36px;
            height: 36px;
            object-fit: contain;
        }

        .login-title {
            font-size: 1.45rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 8px;
            color: #1f2937;
        }

        .login-subtitle {
            text-align: center;
            color: #6b7280;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 24px;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .input-group {
            display: flex;
            align-items: stretch;
            border: 1px solid #dbe3f0;
            border-radius: 14px;
            overflow: hidden;
            background: rgba(255,255,255,.88);
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .input-group:focus-within {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.05rem rgba(13,110,253,.15);
            background: #fff;
        }

        .input-group-text {
            background: transparent;
            border: 0;
            color: #6c757d;
            min-width: 48px;
            justify-content: center;
        }

        .form-control {
            height: 52px;
            border: 0 !important;
            background: transparent;
            box-shadow: none !important;
        }

        .form-control:focus {
            border: 0 !important;
            box-shadow: none !important;
            background: transparent;
        }

        .password-toggle-btn {
            background: transparent;
            border: 0;
            color: #6c757d;
            width: 52px;
        }

        .password-toggle-btn:focus,
        .password-toggle-btn:active {
            box-shadow: none !important;
            border: 0 !important;
            outline: none;
        }

        .btn-login {
            height: 50px;
            border-radius: 14px;
            font-weight: 600;
            font-size: 1rem;
            background: linear-gradient(180deg, #1f2937 0%, #111827 100%);
            border: none;
        }

        .btn-login:hover {
            background: linear-gradient(180deg, #111827 0%, #0b1220 100%);
        }

        .login-footer {
            text-align: center;
            margin-top: 22px;
            font-size: 0.85rem;
            color: #6b7280;
        }

        .alert {
            border-radius: 14px;
            font-size: 0.92rem;
        }

        @media (max-width: 576px) {
            .login-card {
                padding: 26px 20px 20px;
                border-radius: 20px;
            }

            .login-title {
                font-size: 1.25rem;
            }

            .login-subtitle {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

<div class="login-page">
    <div class="login-card">
        <div class="login-logo">
            <img src="{{ asset('images/logo_fix.png') }}" alt="Logo SIMANIS">
        </div>

        <div class="login-title">Sistem Manajemen Inventaris</div>
        <div class="login-subtitle">
            Login untuk mengakses SIMANIS BPS Provinsi DKI Jakarta
        </div>

        @if($errors->any())
            <div class="alert alert-danger mb-3">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email"
                           name="email"
                           class="form-control"
                           value="{{ old('email') }}"
                           placeholder="contoh@email.com"
                           required
                           autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-lock"></i>
                    </span>

                    <input id="password"
                           type="password"
                           name="password"
                           class="form-control"
                           placeholder="Masukkan password"
                           required>

                    <button class="btn password-toggle-btn"
                            type="button"
                            id="togglePassword"
                            aria-label="Tampilkan password">
                        <i class="bi bi-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>
            </div>

            <button class="btn btn-dark btn-login w-100">
                Login
            </button>
        </form>

        <div class="login-footer">
            Copyright © Tim IT BPS Provinsi DKI Jakarta
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