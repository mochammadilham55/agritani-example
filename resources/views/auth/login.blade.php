@extends('layouts.guest')

@section('title', 'Masuk — AgriTani')

@section('content')
    <h1 class="h4 fw-bold text-brand-dark">Masuk</h1>
    <p class="small text-secondary mb-4">Masuk untuk berbelanja atau mengelola hasil tani Anda.</p>

    @if (session('status'))
        <div class="alert alert-success py-2 small">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label small fw-medium">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="form-control">
            @error('email')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="password" class="form-label small fw-medium">Password</label>
            <x-password-input id="password" name="password" required />
            @error('password')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="d-flex align-items-center justify-content-between small mb-3">
            <div class="form-check mb-0">
                <input type="checkbox" name="remember" id="remember" class="form-check-input">
                <label for="remember" class="form-check-label text-secondary">Ingat saya</label>
            </div>
            <a href="{{ route('password.request') }}" class="fw-semibold">Lupa password?</a>
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-semibold">Masuk</button>
    </form>

    <p class="mt-4 mb-0 text-center small text-secondary">
        Belum punya akun? <a href="{{ route('register') }}" class="fw-semibold">Daftar</a>
    </p>
@endsection
