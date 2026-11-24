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

    @if (config('agritani.admin_whatsapp'))
        <div class="text-center mt-4 pt-3 border-top">
            <p class="small text-secondary mb-2">Tidak menerima email?</p>
            <a id="wa-admin" href="{{ 'https://wa.me/'.config('agritani.admin_whatsapp').'?text='.rawurlencode('Halo Admin AgriTani, saya lupa password akun dengan email: ') }}"
               data-wa-number="{{ config('agritani.admin_whatsapp') }}" data-wa-email-input="#email"
               target="_blank" rel="noopener" class="btn btn-outline-success w-100 fw-semibold">Hubungi Admin via WhatsApp</a>
            <p class="small text-secondary mt-2 mb-0">Kirim dari nomor HP yang terdaftar di akun agar admin dapat memverifikasi.</p>
        </div>
    @endif

    <p class="mt-4 mb-0 text-center small">
        <a href="{{ route('login') }}" class="fw-semibold">← Kembali ke halaman masuk</a>
    </p>
@endsection
