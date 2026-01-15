<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Dashboard')</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { background-color: #f4f6f9; }
        .sidebar { width: 260px; }
        .sidebar .nav-link { border-radius: 8px; padding: 10px 12px; font-size: 0.95rem; display: flex; align-items: center; gap: 10px; }
        .sidebar .nav-link i { font-size: 1.1rem; }
        .sidebar .nav-link.active { background-color: #eef2ff; color: #0d6efd !important; font-weight: 600; }
        .sidebar-section { font-size: 0.75rem; font-weight: 600; color: #9ca3af; margin-top: 20px; margin-bottom: 6px; padding-left: 12px; }
        .topbar { background-color: #fff; height: 64px; padding: 0 24px; display: flex; align-items: center; justify-content: space-between; color: #fff; }
        .icon-btn { color: #fff; font-size: 1.2rem; cursor: pointer; }
        .avatar { width: 32px; height: 32px; border-radius: 50%; background-color: #fff; color: #6777ef; display: flex; align-items: center; justify-content: center; font-weight: 600; }
        .page-content { padding: 24px; min-height: calc(100vh - 64px); }
        .filter-select { width: 220px; }
        @media (max-width: 768px) { .filter-select { width: 100%;} }
    </style>
</head>
<body>

<div class="d-flex">

    {{-- SIDEBAR --}}
    @include('layouts.sidebar')

    {{-- CONTENT --}}
    <main class="flex-fill">

        {{-- TOPBAR --}}
        @include('layouts.topbar')

        {{-- PAGE CONTENT --}}
        <div class="page-content">
            @yield('content')
        </div>

    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
