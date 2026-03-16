<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root{
            --sidebar-w: 260px;
            --sidebar-mini-w: 86px;
            --topbar-h: 64px;
        }

        html, body { height: 100%; }

        body{
            background-color: #f4f6f9;
            overflow: hidden;
        }

        /* ===== SIDEBAR BASE ===== */
        #appSidebar{
            width: var(--sidebar-w);
            height: 100vh;
            overflow: hidden;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            transition: all .25s ease;
            background: #fff;
            border-right: 1px solid #e9ecef;
        }

        #appSidebar .nav-link{
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
            transition: all .2s ease;
        }

        #appSidebar .nav-link i{
            font-size: 1.1rem;
            min-width: 20px;
            text-align: center;
        }

        #appSidebar .nav-link.active{
            background-color: #eef2ff;
            color: #0d6efd !important;
            font-weight: 600;
        }

        .sidebar{
            height: 100vh;
            overflow: hidden;
        }

        .sidebar-header{
            flex-shrink: 0;
        }

        .sidebar-logo{
            height: 40px;
            width: auto;
            transition: all .25s ease;
        }

        .sidebar-brand,
        .sidebar-label,
        .sidebar-section{
            transition: opacity .2s ease;
        }

        .sidebar-menu{
            flex: 1 1 auto;
            overflow-y: auto;
            min-height: 0;
        }

        .sidebar-menu::-webkit-scrollbar{
            width: 6px;
        }

        .sidebar-menu::-webkit-scrollbar-thumb{
            background: #d7dce3;
            border-radius: 999px;
        }

        .sidebar-section{
            font-size: 0.75rem;
            font-weight: 600;
            color: #9ca3af;
            margin-top: 20px;
            margin-bottom: 6px;
            padding-left: 12px;
        }

        .sidebar-footer{
            flex-shrink: 0;
            background: #fff;
            border-top: 1px solid #e9ecef;
        }

        /* ===== BACKDROP ===== */
        .sidebar-backdrop{
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.35);
            z-index: 1035;
            display: none;
        }

        .sidebar-backdrop.show{
            display: block;
        }

        /* ===== TOPBAR ===== */
        .topbar{
            background-color: #fff;
            height: var(--topbar-h);
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e9ecef;
        }

        .app-topbar{
            position: sticky;
            top: 0;
            z-index: 1020;
            background: #fff;
        }

        .topbar-wrap{
            position: relative;
        }

        .sidebar-toggle-btn{
            position: absolute;
            top: 50%;
            left: 16px;
            transform: translateY(-50%);
            z-index: 1031;
            display: none;
        }

        .icon-btn{
            color: #fff;
            font-size: 1.2rem;
            cursor: pointer;
        }

        .avatar{
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #fff;
            color: #6777ef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* ===== MAIN WRAPPER ===== */
        .app-main{
            margin-left: var(--sidebar-w);
            height: 100vh;
            display: flex;
            flex-direction: column;
            min-width: 0;
            transition: margin-left .25s ease;
        }

        .page-content{
            padding: 24px;
            flex: 1;
            overflow-y: auto;
            min-height: 0;
        }

        .filter-select { width: 220px; }

        @media (max-width: 768px) {
            .filter-select { width: 100%; }
        }

        /* ===== Tabs ===== */
        .nav-tabs .nav-link{
            color: #000;
            font-weight: 600;
            border: 1px solid transparent;
            border-bottom: 0;
            border-radius: 10px 10px 0 0;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-tabs .nav-link:hover { color: #0d6efd; }

        .nav-tabs .nav-link.active{
            color: #0d6efd !important;
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
            position: relative;
        }

        .nav-tabs .nav-link.active::after{
            content: "";
            position: absolute;
            left: 12px;
            right: 12px;
            bottom: -2px;
            height: 3px;
            background: #0d6efd;
            border-radius: 2px;
        }

        .nav-tabs .nav-link i { color: inherit; }
        .nav-tabs .nav-link:not(.active) { background-color: #f8f9fa; }

        /* ===== Placeholder ===== */
        .form-control::placeholder{
            font-weight: 400;
            opacity: 0.55;
            font-size: 0.90rem;
        }

        .section-title{
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: .75rem;
        }

        .option-card{
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 1rem;
            background: #fafbfc;
        }

        .option-label{
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-weight: 600;
            background: #0d6efd;
            color: #fff;
        }

        .pagination{ gap: 6px; }

        .pagination .page-item .page-link{
            border-radius: 10px;
            font-weight: 600;
            min-width: 38px;
            text-align: center;
        }

        .pagination .active .page-link{
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        /* ===== TABLET ===== */
        @media (max-width: 991.98px){
            #appSidebar{
                width: var(--sidebar-mini-w);
            }

            .app-main{
                margin-left: var(--sidebar-mini-w);
            }

            #appSidebar .sidebar-header{
                justify-content: center !important;
            }

            #appSidebar .sidebar-brand,
            #appSidebar .sidebar-label,
            #appSidebar .sidebar-section{
                display: none !important;
            }

            #appSidebar .nav-link{
                justify-content: center;
                padding: 12px;
                gap: 0;
            }

            #appSidebar .nav-link i{
                font-size: 1.15rem;
                margin: 0;
            }

            #appSidebar .sidebar-footer .btn{
                padding-left: 0;
                padding-right: 0;
            }
        }

        /* ===== MOBILE ===== */
        @media (max-width: 767.98px){
            #appSidebar{
                width: var(--sidebar-w);
                transform: translateX(-100%);
                box-shadow: 0 0 30px rgba(0,0,0,.12);
            }

            #appSidebar.mobile-open{
                transform: translateX(0);
            }

            .app-main{
                margin-left: 0;
            }

            .sidebar-toggle-btn{
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            #appSidebar .sidebar-brand,
            #appSidebar .sidebar-label,
            #appSidebar .sidebar-section{
                display: block !important;
            }

            #appSidebar .nav-link{
                justify-content: flex-start;
                gap: 10px;
                padding: 10px 12px;
            }

            #appSidebar .nav-link i{
                margin: 0;
            }

            .topbar{
                padding-left: 64px;
            }
        }
    </style>
</head>

<body>

    {{-- SIDEBAR --}}
    @include('layouts.sidebar')

    {{-- BACKDROP MOBILE --}}
    <div id="sidebarBackdrop" class="sidebar-backdrop"></div>

    {{-- MAIN --}}
    <main class="app-main">

        {{-- TOPBAR --}}
        <div class="app-topbar topbar-wrap">
            @include('layouts.topbar')
        </div>

        {{-- PAGE CONTENT --}}
        <div class="page-content">
            @yield('content')
        </div>

        {{-- Global Delete Alert --}}
        <x-delete-alert />

        {{-- Global Success Alert --}}
        <x-success-alert />

        @stack('scripts')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // DELETE MODAL
        const deleteModalEl = document.getElementById('globalDeleteModal');

        if (deleteModalEl) {
            const deleteModal = new bootstrap.Modal(deleteModalEl);
            const deleteForm = document.getElementById('deleteForm');
            const title = document.getElementById('deleteTitle');
            const message = document.getElementById('deleteMessage');

            document.addEventListener('click', (e) => {
                const trigger = e.target.closest('.js-delete');
                if (!trigger) return;

                const action = trigger.dataset.action || trigger.getAttribute('action') || '';
                if (!action || !deleteForm) return;

                deleteForm.action = action;
                if (title) title.textContent = trigger.dataset.title || 'Hapus data?';
                if (message) message.textContent = trigger.dataset.message || 'Data akan terhapus permanen.';
                deleteModal.show();
            });
        }

        // SUCCESS MODAL
        const successModalEl = document.getElementById('globalSuccessModal');
        if (successModalEl) {
            const successText = @json(session('success'));
            if (successText) {
                const msgEl = document.getElementById('successMessage');
                if (msgEl) msgEl.textContent = successText;

                new bootstrap.Modal(successModalEl).show();
            }
        }

        // SIDEBAR TOGGLE MOBILE
        const sidebar = document.getElementById('appSidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');

        function openSidebar() {
            if (!sidebar) return;
            sidebar.classList.add('mobile-open');
            sidebarBackdrop?.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            if (!sidebar) return;
            sidebar.classList.remove('mobile-open');
            sidebarBackdrop?.classList.remove('show');
            document.body.style.overflow = '';
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function () {
                if (!sidebar) return;

                if (sidebar.classList.contains('mobile-open')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });
        }

        if (sidebarBackdrop) {
            sidebarBackdrop.addEventListener('click', function () {
                closeSidebar();
            });
        }

        window.addEventListener('resize', function () {
            if (window.innerWidth > 767.98) {
                sidebar?.classList.remove('mobile-open');
                sidebarBackdrop?.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    });
    </script>
</body>
</html>