@extends('layouts.app')

@section('title', 'Daftar Pengguna')

@section('content')

    <style>
        /* ===== TABLE MODERN (samakan dengan Asset) ===== */
        .table-modern thead th{
            background:#f8f9fa;
            font-weight:700;
            border-bottom:1px solid #e9ecef;
            white-space: nowrap;
        }
        .table-modern tbody tr:hover{ background:#f6f9ff; }

        /* ===== ICON BUTTON (samakan dengan Asset) ===== */
        .btn-icon{
            width:38px; height:38px;
            display:inline-flex; align-items:center; justify-content:center;
            border-radius:10px;
        }

        .text-muted-sm{ color:#6c757d; font-size:.85rem; }

        /* optional: bikin select filter sedikit lebih enak */
        .filter-select{ min-width: 220px; }
    </style>

    <h4 class="mb-4">Daftar Pengguna</h4>

    {{-- CARD RECAP --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-people fs-3 text-primary"></i>
                    <h6 class="mt-2">Total Administrator</h6>
                    <h4>{{ $totalAdmin }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-people fs-3 text-success"></i>
                    <h6 class="mt-2">Total User</h6>
                    <h4>{{ $totalUser }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- ACTION BAR + FILTER --}}
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">

        {{-- FILTER FORM --}}
        <form method="GET" class="d-flex align-items-center gap-2">

            <select name="role" class="form-select filter-select">
                <option value="">Pilih peran...</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>
                    Administrator
                </option>
                <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>
                    User
                </option>
            </select>

            <button class="btn btn-primary">
                Cari
            </button>

            <a href="{{ route('admin.users.index') }}" class="btn btn-danger">
                Reset
            </a>

        </form>

        {{-- TAMBAH DATA --}}
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Tambah Data
        </a>

    </div>

    {{-- TABLE --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                    <tr>
                        <th style="width:60px;">#</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th style="width:140px;">Peran</th>
                        <th style="width:190px;">Tanggal Ditambahkan</th>
                        <th style="width:160px;" class="text-center">Aksi</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                <div class="fw-semibold">{{ $user->name }}</div>
                                @if(!empty($user->nip))
                                    <div class="text-muted-sm">
                                        <i class="bi bi-person-badge"></i> NIP: {{ $user->nip }}
                                    </div>
                                @endif
                            </td>

                            <td>{{ $user->email }}</td>

                            <td>
                                @if($user->role === 'admin')
                                    <span class="badge rounded-pill text-bg-primary">Admin</span>
                                @else
                                    <span class="badge rounded-pill text-bg-success">User</span>
                                @endif
                            </td>

                            <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>

                            <td class="text-center">
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('admin.users.edit',$user) }}"
                                       class="btn btn-warning btn-icon"
                                       title="Edit User">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <button class="btn btn-danger btn-icon text-white js-delete"
                                            data-action="{{ route('admin.users.destroy', $user) }}"
                                            data-title="Delete User?"
                                            data-message="Data User akan terhapus permanen.">
                                        <i class="bi bi-trash"></i>
                                    </button>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted p-4">
                                Belum ada data pengguna.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- kalau nanti users jadi paginate, tinggal aktifkan ini --}}
        {{-- @if(method_exists($users, 'links'))
            <div class="card-footer bg-white">
                {{ $users->links() }}
            </div>
        @endif --}}
    </div>

@endsection
