<div class="topbar border-bottom">

    {{-- LEFT --}}
    <div class="d-flex align-items-center gap-3">
    </div>

    {{-- RIGHT --}}
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center gap-2 text-dark text-decoration-none"
           data-bs-toggle="dropdown">
            <div class="avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <span>Hi, {{ auth()->user()->name }}</span>
        </a>
    </div>

</div>
