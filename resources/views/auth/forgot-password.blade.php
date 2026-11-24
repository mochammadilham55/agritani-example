@extends('layouts.guest')

@section('title', 'Lupa Password — AgriTani')

@section('content')
    <h1 class="h4 fw-bold text-brand-dark">Lupa Password</h1>
    <p class="small text-secondary mb-4">Hubungi admin untuk mengatur ulang password. Isi email akun Anda agar ikut terkirim di pesan.</p>

    <div class="mb-3">
        <label for="email" class="form-label small fw-medium">Email akun</label>
        <input id="email" type="email" value="{{ old('email') }}" autofocus class="form-control" placeholder="nama@email.com">
    </div>

    @if (config('agritani.admin_whatsapp'))
        <a id="wa-admin" href="{{ 'https://wa.me/'.config('agritani.admin_whatsapp').'?text='.rawurlencode('Halo Admin AgriTani, saya lupa password akun dengan email: ') }}"
           data-wa-number="{{ config('agritani.admin_whatsapp') }}" data-wa-email-input="#email"
           target="_blank" rel="noopener" class="btn btn-primary w-100 fw-semibold">Hubungi Admin via WhatsApp</a>
        <p class="small text-secondary mt-2 mb-0">Kirim dari nomor HP yang terdaftar di akun agar admin dapat memverifikasi. Admin akan memberi password sementara yang wajib Anda ganti setelah masuk.</p>
    @else
        <div class="alert alert-secondary small mb-0">Silakan hubungi admin AgriTani untuk mengatur ulang password Anda.</div>
    @endif

    <p class="mt-4 mb-0 text-center small">
        <a href="{{ route('login') }}" class="fw-semibold">← Kembali ke halaman masuk</a>
    </p>
@endsection
