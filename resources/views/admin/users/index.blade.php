@extends('layouts.app')

@section('title', 'Kelola Pengguna')
@section('heading', 'Kelola Pengguna')

@section('content')
    @if ($temp = session('temporary_password'))
        <div class="card border-warning mb-4">
            <div class="card-body">
                <h2 class="h6 fw-bold mb-2">Password sementara untuk {{ $temp['name'] }}</h2>
                <p class="small text-secondary mb-2">{{ $temp['email'] }} · Hanya ditampilkan sekali. Kirim ke pengguna, lalu minta ia login dan langsung menggantinya.</p>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <code id="temp-password" class="fs-5 px-3 py-2 bg-light border rounded">{{ $temp['password'] }}</code>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-copy="#temp-password">Salin</button>
                </div>
            </div>
        </div>
    @endif

    <p class="small text-secondary">Gunakan halaman ini saat pengguna menghubungi admin karena lupa password. Cocokkan dulu nomor WhatsApp pengirim dengan No. HP di akun.</p>

    <form method="GET" class="d-flex flex-wrap align-items-center gap-2 mb-4">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, email, atau no. HP…" class="form-control flex-grow-1" style="min-width: 12rem; width: auto;" aria-label="Cari pengguna">
        <select name="role" class="form-select w-auto" onchange="this.form.submit()" aria-label="Peran">
            <option value="">Semua peran</option>
            <option value="pembeli" @selected(request('role') === 'pembeli')>Pembeli</option>
            <option value="petani" @selected(request('role') === 'petani')>Petani</option>
        </select>
        <button class="btn btn-outline-secondary">Cari</button>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead>
                    <tr><th>Pengguna</th><th>No. HP</th><th>Peran</th><th>Status</th><th class="text-end">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($users as $u)
                        <tr>
                            <td class="fw-medium">{{ $u->name }}<span class="d-block small fw-normal text-secondary">{{ $u->email }}</span></td>
                            <td class="text-secondary">{{ $u->phone ?? '-' }}</td>
                            <td>{{ ucfirst($u->role) }}</td>
                            <td>
                                <x-badge :status="$u->is_active ? 'aktif' : 'ditangguhkan'" :label="$u->is_active ? 'Aktif' : 'Belum aktif'" />
                                @if ($u->must_change_password)
                                    <span class="d-block small text-secondary mt-1">Memakai password sementara</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.users.reset-password', $u) }}"
                                      onsubmit="return confirm('Reset password pengguna ini? Password lama tidak bisa dipakai lagi.')">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-outline-secondary btn-sm fw-semibold">Reset Password</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-5 text-center text-secondary">Tidak ada pengguna.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
@endsection
