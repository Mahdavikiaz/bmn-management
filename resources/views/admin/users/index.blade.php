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
                <h4>{{ $users->where('role','admin')->count() }}</h4>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-people fs-3 text-success"></i>
                <h6 class="mt-2">Total User</h6>
                <h4>{{ $users->where('role','user')->count() }}</h4>
            </div>
        </div>
    </div>
</div>

{{-- ACTION BAR --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#filterBox">
        Menu Filter
    </button>

    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        + Tambah Data
    </a>
</div>

{{-- FILTER --}}
<div class="collapse mb-3" id="filterBox">
    <form method="GET" class="card card-body">
        <div class="row g-2">
            <div class="col-md-4">
                <select name="role" class="form-select">
                    <option value="">Pilih peran...</option>
                    <option value="admin">Administrator</option>
                    <option value="user">User</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">Cari</button>
            </div>
        </div>
    </form>
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
                        <span class="badge bg-{{ $user->role == 'admin' ? 'primary' : 'secondary' }}">
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
