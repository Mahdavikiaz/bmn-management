<aside class="bg-white border-end vh-100 d-flex flex-column sidebar">

    <div class="p-3 fw-bold fs-5 text-center text-primary">
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
                <a href="{{ route('admin.assets.index') }}" 
                    class="nav-link {{ request()->is('admin/assets*') ? 'active' : 'text-dark' }}">
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
                <a href="{{ route('admin.users.index') }}"
                   class="nav-link {{ request()->is('admin/users*') ? 'active' : 'text-dark' }}">
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
