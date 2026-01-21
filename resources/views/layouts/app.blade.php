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
        :root{
            --sidebar-w: 260px;
            --topbar-h: 64px;
        }

        html, body { height: 100%; }
        body {
            background-color: #f4f6f9;
            overflow: hidden;
        }

        /* ===== SIDEBAR ===== */
        .sidebar { width: var(--sidebar-w); }
        .sidebar .nav-link {
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar .nav-link i { font-size: 1.1rem; }
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

        .app-sidebar{
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-w);
            height: 100vh;
            overflow-y: hidden;
            background: #fff;
            border-right: 1px solid #e9ecef;
            z-index: 1030;
            padding-bottom: 12px;
        }

        .app-sidebar *{
            max-width: 100%;
        }

        /* ===== TOPBAR ===== */
        .topbar {
            background-color: #fff;
            height: var(--topbar-h);
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #fff;
            border-bottom: 1px solid #e9ecef;
        }

        .app-topbar{
            position: sticky;
            top: 0;
            z-index: 1020;
            background: #fff;
        }

        .icon-btn { color: #fff; font-size: 1.2rem; cursor: pointer; }
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
        }

        /* MAIN WRAPPER */
        .app-main{
            margin-left: var(--sidebar-w);
            height: 100vh;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        /* Content scroll */
        .page-content{
            padding: 24px;
            flex: 1;
            overflow-y: auto;
            min-height: 0;
        }

        .filter-select { width: 220px; }
        @media (max-width: 768px) { .filter-select { width: 100%; } }

        /* ===== Tabs ===== */
        .nav-tabs .nav-link {
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
        .nav-tabs .nav-link.active {
            color: #0d6efd !important;
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
            position: relative;
        }
        .nav-tabs .nav-link.active::after {
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
        .form-control::placeholder {
            font-weight: 400;
            opacity: 0.55;
            font-size: 0.90rem;
        }

        .section-title {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: .75rem;
        }
        .option-card {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 1rem;
            background: #fafbfc;
        }
        .option-label {
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
    </style>
</head>

<body>

    {{-- SIDEBAR --}}
    <aside class="app-sidebar">
        @include('layouts.sidebar')
    </aside>

    {{-- MAIN --}}
    <main class="app-main">

        {{-- TOPBAR STICKY --}}
        <div class="app-topbar">
            @include('layouts.topbar')
        </div>

        {{-- PAGE CONTENT (yang scroll) --}}
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

                // event delegation
                document.addEventListener('click', (e) => {
                    const btn = e.target.closest('.js-delete');
                    if (!btn) return;

                    if (deleteForm) deleteForm.action = btn.dataset.action || '';
                    if (title) title.textContent = btn.dataset.title || 'Delete data?';
                    if (message) message.textContent = btn.dataset.message || 'Are you sure?';
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

        });
    </script>

</body>
</html>
