@extends('layouts.guest')

@section('title', 'Daftar — Tanimart')

@section('content')
    <h1 class="h4 fw-bold text-brand-dark">Buat Akun</h1>
    <p class="small text-secondary mb-4">Daftar sebagai pembeli atau petani.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="mb-3">
            <span class="form-label small fw-medium d-block">Daftar sebagai</span>
            <div class="row g-2">
                @foreach (['pembeli' => 'Pembeli', 'petani' => 'Petani'] as $value => $label)
                    <div class="col-6">
                        <label class="d-block">
                            <input type="radio" name="role" value="{{ $value }}" class="choice-input visually-hidden"
                                   @checked(old('role', 'pembeli') === $value)>
                            <span class="choice-box py-2 text-center small">{{ $label }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
            @error('role')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
            <div class="form-text">Akun petani aktif setelah diverifikasi admin.</div>
        </div>

        @foreach ([['name', 'Nama lengkap', 'text'], ['email', 'Email', 'email'], ['phone', 'No. HP (opsional)', 'text']] as [$field, $label, $type])
            <div class="mb-3">
                <label for="{{ $field }}" class="form-label small fw-medium">{{ $label }}</label>
                <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ old($field) }}" @required($field !== 'phone') class="form-control">
                @error($field)<div class="small text-danger mt-1">{{ $message }}</div>@enderror
            </div>
        @endforeach

        <div id="bank-fields" class="{{ old('role') === 'petani' ? '' : 'd-none' }}">
            <p class="small fw-semibold text-brand-dark mb-2">Rekening penerima transfer</p>
            @foreach ([['bank_name', 'Nama bank (mis. BCA, BRI)'], ['bank_account_number', 'Nomor rekening'], ['bank_account_name', 'Atas nama']] as [$field, $label])
                <div class="mb-3">
                    <label for="{{ $field }}" class="form-label small fw-medium">{{ $label }}</label>
                    <input id="{{ $field }}" name="{{ $field }}" type="text" value="{{ old($field) }}" @if ($field === 'bank_account_number') inputmode="numeric" @endif class="form-control">
                    @error($field)<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                </div>
            @endforeach
        </div>

        <div class="mb-3">
            <label for="password" class="form-label small fw-medium">Password</label>
            <x-password-input id="password" name="password" required minlength="8" />
            @error('password')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label small fw-medium">Konfirmasi password</label>
            <x-password-input id="password_confirmation" name="password_confirmation" required />
        </div>

        <button type="submit" class="btn btn-primary w-100 fw-semibold">Daftar</button>
    </form>

    <p class="mt-4 mb-0 text-center small text-secondary">
        Sudah punya akun? <a href="{{ route('login') }}" class="fw-semibold">Masuk</a>
    </p>
@endsection
