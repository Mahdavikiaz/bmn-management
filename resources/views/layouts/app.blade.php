<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Dashboard')</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Optional Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="d-flex">
    {{-- SIDEBAR --}}
    <aside class="bg-white border-end vh-100 p-3" style="width:260px;">
        <h5 class="fw-bold mb-4">INVENBMN</h5>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-dark" href="#">Dashboard</a>
            </li>

            <li class="nav-item mt-2 text-muted small">MANAJEMEN</li>

            <li class="nav-item">
                <a class="nav-link text-primary fw-semibold" href="{{ route('admin.users.index') }}">
                    Data Pengguna
                </a>
            </li>
        </ul>

        <form method="POST" action="{{ route('logout') }}" class="mt-auto">
            @csrf
            <button class="btn btn-danger w-100 mt-4">Logout</button>
        </form>
    </aside>

    {{-- CONTENT --}}
    <main class="flex-fill p-4">
        {{-- TOPBAR --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <button class="btn btn-outline-secondary">
                <i class="bi bi-list"></i>
            </button>

            <div>
                Halo, <strong>{{ auth()->user()->name }}</strong>
            </div>
        </div>

        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
