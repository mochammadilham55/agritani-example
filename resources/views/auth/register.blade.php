@extends('layouts.guest')

@section('title', 'Daftar — AgriTani')

@section('content')
    <h1 class="text-xl font-bold text-brand-dark">Buat Akun</h1>
    <p class="mb-6 mt-1 text-sm text-gray-500">Daftar sebagai pembeli atau petani.</p>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <div>
            <span class="mb-1 block text-sm font-medium">Daftar sebagai</span>
            <div class="grid grid-cols-2 gap-2">
                @foreach (['pembeli' => 'Pembeli', 'petani' => 'Petani'] as $value => $label)
                    <label class="cursor-pointer">
                        <input type="radio" name="role" value="{{ $value }}" class="peer sr-only"
                               @checked(old('role', 'pembeli') === $value)>
                        <span class="block rounded-lg border border-line py-2 text-center text-sm peer-checked:border-brand peer-checked:bg-role-bg peer-checked:font-semibold peer-checked:text-brand-dark">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            @error('role')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
            <p class="mt-1 text-xs text-gray-500">Akun petani aktif setelah diverifikasi admin.</p>
        </div>

        @foreach ([['name', 'Nama lengkap', 'text'], ['email', 'Email', 'email'], ['phone', 'No. HP (opsional)', 'text']] as [$field, $label, $type])
            <div>
                <label for="{{ $field }}" class="mb-1 block text-sm font-medium">{{ $label }}</label>
                <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ old($field) }}" @required($field !== 'phone')
                       class="w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                @error($field)<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
            </div>
        @endforeach

        <div>
            <label for="password" class="mb-1 block text-sm font-medium">Password</label>
            <input id="password" name="password" type="password" required minlength="8"
                   class="w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
            @error('password')<p class="mt-1 text-xs text-[#8E1F14]">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-medium">Konfirmasi password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required
                   class="w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
        </div>

        <button type="submit" class="w-full rounded-lg bg-brand py-2.5 text-sm font-semibold text-white hover:bg-brand-dark">Daftar</button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-brand hover:underline">Masuk</a>
    </p>
@endsection
