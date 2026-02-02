@extends('layouts.app')

@section('title', 'Edit User')

@section('content')

<div class="mb-4">
    <h4>Edit User</h4>
    <p class="text-muted">Perbarui data pengguna</p>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')

            {{-- Nama --}}
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text"
                       name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $user->name) }}"
                       required>

                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Email (readonly) --}}
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email"
                       class="form-control"
                       value="{{ $user->email }}"
                       readonly>
                <small class="text-muted">
                    Email tidak dapat diubah
                </small>
            </div>

            {{-- Password --}}
            <div class="mb-3">
                <label class="form-label">Password</label>

                <input type="password"
                    name="password"
                    id="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="Masukkan password baru"
                    disabled>

                @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror

                <div class="form-check mt-2">
                    <input class="form-check-input"
                        type="checkbox"
                        id="changePassword">
                    <label class="form-check-label" for="changePassword">
                        Ubah Password
                    </label>
                </div>

                <small class="text-muted">
                    Password lama tidak ditampilkan demi keamanan
                </small>
            </div>

            {{-- Role --}}
            <div class="mb-4">
                <label class="form-label">Peran</label>
                <select name="role"
                        class="form-select @error('role') is-invalid @enderror"
                        required>
                    <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>
                        Administrator
                    </option>
                    <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>
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

                <button type="submit" class="btn btn-warning">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>
<script>
    const checkbox = document.getElementById('changePassword');
    const passwordInput = document.getElementById('password');

    checkbox.addEventListener('change', function () {
        if (this.checked) {
            passwordInput.disabled = false;
            passwordInput.focus();
        } else {
            passwordInput.disabled = true;
            passwordInput.value = '';
        }
    });
</script>

@endsection
