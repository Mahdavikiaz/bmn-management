@extends('layouts.app')

@section('title', 'Daftar Pengguna')

@section('content')

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
        + Tambah Data
    </a>

</div>


{{-- TABLE --}}
<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Nama Lengkap</th>
                    <th>Email</th>
                    <th>Peran</th>
                    <th>Tanggal Ditambahkan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge bg-{{ $user->role == 'admin' ? 'primary' : 'success' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.users.edit',$user) }}" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i>
                        </a>

                        <form method="POST" action="{{ route('admin.users.destroy',$user) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger"
                                onclick="return confirm('Yakin hapus user?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
