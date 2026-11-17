@extends('layouts.app')

@section('title', 'Profil')
@section('heading', 'Profil')

@section('content')
    <div class="row g-4" style="max-width: 62rem;">
        <div class="col-lg-6">
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="card">
                @csrf @method('PUT')
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold text-brand-dark mb-3">Data Diri</h2>

                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-role-bg d-flex align-items-center justify-content-center overflow-hidden fs-3 flex-shrink-0" style="width: 4rem; height: 4rem;">
                            @if ($user->photo)<img src="{{ asset('storage/'.$user->photo) }}" alt="Foto profil" class="w-100 h-100 object-fit-cover">@else 👤 @endif
                        </div>
                        <div class="flex-grow-1">
                            <input type="file" name="photo" accept="image/png,image/jpeg,image/webp" class="form-control form-control-sm">
                            @error('photo')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    @foreach ([['name', 'Nama', 'text'], ['email', 'Email', 'email'], ['phone', 'No. HP', 'text']] as [$f, $label, $type])
                        <div class="mb-3">
                            <label for="{{ $f }}" class="form-label small fw-medium">{{ $label }}</label>
                            <input id="{{ $f }}" name="{{ $f }}" type="{{ $type }}" value="{{ old($f, $user->$f) }}" @required($f !== 'phone') class="form-control">
                            @error($f)<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                        </div>
                    @endforeach

                    <div class="mb-3">
                        <label for="address" class="form-label small fw-medium">Alamat</label>
                        <textarea id="address" name="address" rows="3" class="form-control">{{ old('address', $user->address) }}</textarea>
                        @error('address')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                    </div>

                    <button class="btn btn-primary fw-semibold px-4">Simpan</button>
                </div>
            </form>
        </div>

        <div class="col-lg-6">
            <form method="POST" action="{{ route('profile.password') }}" class="card">
                @csrf @method('PUT')
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold text-brand-dark mb-3">Ubah Password</h2>
                    @foreach ([['current_password', 'Password saat ini'], ['password', 'Password baru'], ['password_confirmation', 'Konfirmasi password baru']] as [$f, $label])
                        <div class="mb-3">
                            <label for="{{ $f }}" class="form-label small fw-medium">{{ $label }}</label>
                            <x-password-input :id="$f" :name="$f" required />
                            @error($f)<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                        </div>
                    @endforeach
                    <button class="btn btn-outline-secondary fw-semibold px-4">Ganti Password</button>
                </div>
            </form>
        </div>
    </div>
@endsection
