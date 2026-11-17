@extends('layouts.guest')

@section('title', 'Lupa Password — AgriTani')

@section('content')
    <h1 class="h4 fw-bold text-brand-dark">Lupa Password</h1>
    <p class="small text-secondary mb-4">Masukkan email akun Anda. Kami kirim tautan untuk mengatur ulang password.</p>

    @if (session('status'))
        <div class="alert alert-success py-2 small">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label small fw-medium">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="form-control">
            @error('email')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-semibold">Kirim Tautan Reset</button>
    </form>

    <p class="mt-4 mb-0 text-center small">
        <a href="{{ route('login') }}" class="fw-semibold">← Kembali ke halaman masuk</a>
    </p>
@endsection
