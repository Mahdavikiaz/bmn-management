<aside class="bg-white border-end vh-100 d-flex flex-column sidebar">

    <div class="p-3 fw-bold fs-5 text-center text-primary">
        SIMANIS
    </div>

    <div class="flex-grow-1 p-3">
        <ul class="nav flex-column gap-1">

            <li class="nav-item">
                <a href="{{ route('admin.dashboard.index') }}" 
                    class="nav-link {{ request()->is('admin/dashboard*') ? 'active' : 'text-dark' }}">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.asset-checks.index') }}"
                   class="nav-link {{ request()->is('admin/asset-checks*') ? 'active' : 'text-dark' }}">
                    <i class="bi bi-clipboard-check"></i>
                    Pengecekan Asset
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.reports.index') }}"
                class="nav-link {{ request()->is('admin/reports*') ? 'active' : 'text-dark' }}">
                    <i class="bi bi-file-earmark-text"></i>
                    Report
                </a>
            </li>

            <div class="sidebar-section">DATA MASTER</div>

            <li class="nav-item">
                <a href="{{ route('admin.asset-types.index') }}"
                   class="nav-link {{ request()->is('admin/asset-types*') ? 'active' : 'text-dark' }}">
                    <i class="bi bi-collection"></i>
                    Data Tipe Asset
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.assets.index') }}"
                   class="nav-link {{ request()->is('admin/assets*') ? 'active' : 'text-dark' }}">
                    <i class="bi bi-laptop"></i>
                    Data Asset
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.indicator-questions.index') }}"
                   class="nav-link {{ request()->is('admin/indicator-questions*') ? 'active' : 'text-dark' }}">
                    <i class="bi bi-ui-checks-grid"></i>
                    Data Indikator
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.recommendations.index') }}"
                   class="nav-link {{ request()->is('admin/recommendations*') ? 'active' : 'text-dark' }}">
                    <i class="bi bi-lightbulb"></i>
                    Data Rekomendasi
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.spareparts.index') }}"
                   class="nav-link {{ request()->is('admin/spareparts*') ? 'active' : 'text-dark' }}">
                    <i class="bi bi-hdd-stack"></i>
                    Data Sparepart
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.users.index') }}"
                   class="nav-link {{ request()->is('admin/users*') ? 'active' : 'text-dark' }}">
                    <i class="bi bi-people"></i>
                    Data User
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
