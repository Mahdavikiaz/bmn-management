<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

<div class="card shadow-sm" style="width: 400px;">
    <div class="card-body">
        <h4 class="text-center mb-4">Login</h4>

        @if($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email"
                       class="form-control"
                       value="{{ old('email') }}"
                       required autofocus>
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password"
                       class="form-control"
                       required>
            </div>

            <button class="btn btn-primary w-100">
                Login
            </button>
        </form>
    </div>
</div>

</body>
</html>
