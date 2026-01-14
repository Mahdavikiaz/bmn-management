@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')

<div class="mb-4">
    <h4>Tambah User</h4>
    <p class="text-muted">Form untuk menambahkan pengguna baru</p>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            {{-- Nama --}}
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text"
                       name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}"
                       required>

                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- NIP --}}
            <div class="mb-3">
                <label class="form-label">NIP</label>
                <input type="text"
                       name="nip"
                       class="form-control @error('nip') is-invalid @enderror"
                       value="{{ old('nip') }}"
                       required>

                @error('nip')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Email --}}
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email"
                       name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}"
                       required>

                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Password --}}
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password"
                       name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       required>

                @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Role --}}
            <div class="mb-4">
                <label class="form-label">Peran</label>
                <select name="role"
                        class="form-select @error('role') is-invalid @enderror"
                        required>
                    <option value="">-- Pilih Peran --</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>
                        Administrator
                    </option>
                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>
                        User
                    </option>
                </select>

                @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Action --}}
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                    Kembali
                </a>

                <button type="submit" class="btn btn-primary">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
