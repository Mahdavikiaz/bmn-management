<aside id="appSidebar" class="bg-white border-end d-flex flex-column sidebar">
    {{-- HEADER / LOGO --}}
    <div class="p-3 d-flex align-items-center justify-content-center gap-2 flex-shrink-0 sidebar-header">
        <img src="{{ asset('images/logo_fix.png') }}"
             alt="Logo"
             class="sidebar-logo">

        <span class="fw-bold fs-5 text-primary sidebar-brand">
            SIMANIS
        </span>
    </div>

    @php
        $authUser = auth()->user();

        $canMaster =
            $authUser?->can('viewAny', \App\Models\AssetType::class) ||
            $authUser?->can('viewAny', \App\Models\IndicatorQuestion::class) ||
            $authUser?->can('viewAny', \App\Models\Recommendation::class) ||
            $authUser?->can('viewAny', \App\Models\Sparepart::class) ||
            $authUser?->can('viewAny', \App\Models\User::class);
    @endphp

    {{-- MENU --}}
    <div class="flex-grow-1 p-3 sidebar-menu">
        <ul class="nav flex-column gap-1">

            {{-- DASHBOARD --}}
            @if($authUser)
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard.index') }}"
                       class="nav-link {{ request()->is('admin/dashboard*') ? 'active' : 'text-dark' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span class="sidebar-label">Dashboard</span>
                    </a>
                </li>
            @endif


            {{-- HASIL PENGECEKAN ASSET --}}
            {{-- Admin, User, dan Viewer boleh melihat hasil pengecekan --}}
            @can('viewAny', \App\Models\PerformanceReport::class)
                <li class="nav-item">
                    <a href="{{ route('admin.asset-checks.index') }}"
                       class="nav-link {{ request()->is('admin/asset-checks*') ? 'active' : 'text-dark' }}">
                        <i class="bi bi-clipboard-check"></i>

                        @if($authUser?->isViewer())
                            <span class="sidebar-label">Hasil Pengecekan Asset</span>
                        @else
                            <span class="sidebar-label">Pengecekan Asset</span>
                        @endif
                    </a>
                </li>
            @endcan


            {{-- PERBAIKAN ASSET --}}
            {{-- Viewer tidak boleh melihat menu ini --}}
            @can('viewAny', \App\Models\Asset::class)
                <li class="nav-item">
                    <a href="{{ route('admin.asset-services.index') }}"
                       class="nav-link {{ request()->is('admin/asset-services*') ? 'active' : 'text-dark' }}">
                        <i class="bi bi-tools"></i>
                        <span class="sidebar-label">Perbaikan Asset</span>
                    </a>
                </li>
            @endcan


            {{-- REPORT --}}
            {{-- Admin, User, dan Viewer boleh melihat dan export report --}}
            @can('viewAny', \App\Models\PerformanceReport::class)
                <li class="nav-item">
                    <a href="{{ route('admin.reports.index') }}"
                       class="nav-link {{ request()->is('admin/reports*') ? 'active' : 'text-dark' }}">
                        <i class="bi bi-file-earmark-text"></i>
                        <span class="sidebar-label">Report</span>
                    </a>
                </li>
            @endcan


            {{-- DATA ASSET --}}
            {{-- Viewer tidak boleh melihat menu ini jika AssetPolicy::viewAny tidak mengizinkan viewer --}}
            @can('viewAny', \App\Models\Asset::class)
                <li class="nav-item">
                    <a href="{{ route('admin.assets.index') }}"
                       class="nav-link {{ request()->is('admin/assets*') ? 'active' : 'text-dark' }}">
                        <i class="bi bi-laptop"></i>
                        <span class="sidebar-label">Data Asset</span>
                    </a>
                </li>
            @endcan


            {{-- DATA MASTER --}}
            {{-- Hanya tampil kalau user punya akses minimal salah satu data master --}}
            @if($canMaster)
                <div class="sidebar-section sidebar-label">DATA MASTER</div>

                @can('viewAny', \App\Models\AssetType::class)
                    <li class="nav-item">
                        <a href="{{ route('admin.asset-types.index') }}"
                           class="nav-link {{ request()->is('admin/asset-types*') ? 'active' : 'text-dark' }}">
                            <i class="bi bi-collection"></i>
                            <span class="sidebar-label">Data Tipe Asset</span>
                        </a>
                    </li>
                @endcan

                @can('viewAny', \App\Models\IndicatorQuestion::class)
                    <li class="nav-item">
                        <a href="{{ route('admin.indicator-questions.index') }}"
                           class="nav-link {{ request()->is('admin/indicator-questions*') ? 'active' : 'text-dark' }}">
                            <i class="bi bi-ui-checks-grid"></i>
                            <span class="sidebar-label">Data Indikator</span>
                        </a>
                    </li>
                @endcan

                @can('viewAny', \App\Models\Recommendation::class)
                    <li class="nav-item">
                        <a href="{{ route('admin.recommendations.index') }}"
                           class="nav-link {{ request()->is('admin/recommendations*') ? 'active' : 'text-dark' }}">
                            <i class="bi bi-lightbulb"></i>
                            <span class="sidebar-label">Data Rekomendasi</span>
                        </a>
                    </li>
                @endcan

                @can('viewAny', \App\Models\Sparepart::class)
                    <li class="nav-item">
                        <a href="{{ route('admin.spareparts.index') }}"
                           class="nav-link {{ request()->is('admin/spareparts*') ? 'active' : 'text-dark' }}">
                            <i class="bi bi-hdd-stack"></i>
                            <span class="sidebar-label">Data Sparepart</span>
                        </a>
                    </li>
                @endcan

                @can('viewAny', \App\Models\User::class)
                    <li class="nav-item">
                        <a href="{{ route('admin.users.index') }}"
                           class="nav-link {{ request()->is('admin/users*') ? 'active' : 'text-dark' }}">
                            <i class="bi bi-people"></i>
                            <span class="sidebar-label">Data User</span>
                        </a>
                    </li>
                @endcan
            @endif

        </ul>
    </div>

    {{-- FOOTER / LOGOUT --}}
    <div class="p-3 border-top flex-shrink-0 bg-white sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-danger w-100 d-flex align-items-center justify-content-center gap-2 sidebar-logout-btn">
                <i class="bi bi-box-arrow-right"></i>
                <span class="sidebar-label">Logout</span>
            </button>
        </form>
    </div>
</aside>
