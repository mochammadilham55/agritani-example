@extends('layouts.guest')

@section('title', 'Atur Ulang Password — AgriTani')

@section('content')
    <h1 class="h4 fw-bold text-brand-dark">Atur Ulang Password</h1>
    <p class="small text-secondary mb-4">Buat password baru untuk akun Anda (minimal 8 karakter).</p>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="mb-3">
            <label for="email" class="form-label small fw-medium">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required class="form-control">
            @error('email')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="password" class="form-label small fw-medium">Password baru</label>
            <x-password-input id="password" name="password" required autofocus />
            @error('password')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label small fw-medium">Konfirmasi password baru</label>
            <x-password-input id="password_confirmation" name="password_confirmation" required />
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-semibold">Simpan Password Baru</button>
    </form>
@endsection
