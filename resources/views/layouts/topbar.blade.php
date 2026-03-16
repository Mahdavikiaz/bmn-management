<div class="topbar border-bottom">

    {{-- LEFT --}}
    <div class="d-flex align-items-center gap-2">
        <button type="button"
                id="sidebarToggle"
                class="btn btn-outline-secondary d-md-none"
                aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>
    </div>

    {{-- RIGHT --}}
    <div class="dropdown">
        <a href="#"
           class="d-flex align-items-center gap-2 text-dark text-decoration-none"
           data-bs-toggle="dropdown">
            <div class="avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <span class="d-none d-sm-inline">Hi, {{ auth()->user()->name }}</span>
        </a>

        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li class="px-3 py-2 text-muted small">
                Login sebagai <strong>{{ auth()->user()->name }}</strong>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </div>

</div>