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
        body {
            background-color: #f4f6f9;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 260px;
        }

        .sidebar .nav-link {
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar .nav-link i {
            font-size: 1.1rem;
        }

        .sidebar .nav-link.active {
            background-color: #eef2ff;
            color: #0d6efd !important;
            font-weight: 600;
        }

        .sidebar-section {
            font-size: 0.75rem;
            font-weight: 600;
            color: #9ca3af;
            margin-top: 20px;
            margin-bottom: 6px;
            padding-left: 12px;
        }

        /* ===== TOPBAR ===== */
        .topbar {
            background-color: #0d6efd;
            height: 64px;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #fff;
            /* penting agar MENEMPEL */
            border-radius: 0;
        }

        .topbar .search-box {
            background: #fff;
            border-radius: 6px;
            padding: 6px 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            width: 280px;
        }

        .topbar .search-box input {
            border: none;
            outline: none;
            font-size: 0.85rem;
            width: 100%;
        }

        .topbar .icon-btn {
            color: #fff;
            font-size: 1.2rem;
            cursor: pointer;
        }

        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #fff;
            color: #6777ef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* ===== PAGE CONTENT ===== */
        .page-content {
            padding: 24px;
            min-height: calc(100vh - 64px);
        }
    </style>
</head>
<body>

<div class="d-flex">

    {{-- SIDEBAR --}}
    <aside class="bg-white border-end vh-100 d-flex flex-column sidebar">
        
        <div class="p-4 fw-bold fs-5 text-center border-bottom">
            INVENBMN
        </div>

        <div class="flex-grow-1 p-3">
            <ul class="nav flex-column gap-1">

                <li class="nav-item">
                    <a href="#" class="nav-link text-dark">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link text-dark">
                        <i class="bi bi-clipboard-check"></i>
                        Pengecekan Asset
                    </a>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link text-dark">
                        <i class="bi bi-file-earmark-text"></i>
                        Report
                    </a>
                </li>

                <div class="sidebar-section">DATA MASTER</div>

                <li class="nav-item">
                    <a href="#" class="nav-link text-dark">
                        <i class="bi bi-laptop"></i>
                        Data Asset
                    </a>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link text-dark">
                        <i class="bi bi-ui-checks-grid"></i>
                        Data Indikator
                    </a>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link text-dark">
                        <i class="bi bi-lightbulb"></i>
                        Data Rekomendasi
                    </a>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link text-dark">
                        <i class="bi bi-hdd-stack"></i>
                        Data Sparepart
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->is('admin/users*') ? 'active' : 'text-dark' }}">
                        <i class="bi bi-people"></i>
                        Data Pengguna
                    </a>
                </li>

            </ul>
        </div>

        <div class="p-3 border-top">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-danger w-100 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-box-arrow-right"></i>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- CONTENT --}}
    <main class="flex-fill">

        {{-- TOPBAR --}}
        <div class="topbar">

            {{-- LEFT --}}
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-list icon-btn"></i>
            </div>

            {{-- RIGHT --}}
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center gap-2 text-white text-decoration-none"
                   data-bs-toggle="dropdown">
                    <div class="avatar">
                        {{ strtoupper(substr(auth()->user()->name,0,1)) }}
                    </div>
                    <span>Hi, {{ auth()->user()->name }}</span>
                    <i class="bi bi-chevron-down"></i>
                </a>

                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#">Profile</a></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>

        </div>

        {{-- PAGE CONTENT --}}
        <div class="page-content">
            @yield('content')
        </div>

    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
