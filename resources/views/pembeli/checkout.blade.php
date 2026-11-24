@extends('layouts.public')

@section('title', 'Checkout — Tanimart')

@section('content')
    <h1 class="fs-2 fw-bold text-brand-dark mb-4">Checkout</h1>

    <form method="POST" action="{{ route('pembeli.checkout.store') }}" class="row g-4">
        @csrf

        <div class="col-lg-8 d-grid gap-4 align-content-start">
            <section class="card">
                <div class="card-body">
                    <h2 class="h6 fw-bold text-brand-dark mb-3">Alamat Pengiriman</h2>
                    <textarea name="shipping_address" rows="3" required placeholder="Nama jalan, nomor, kelurahan, kota, kode pos"
                              class="form-control">{{ old('shipping_address', auth()->user()->address) }}</textarea>
                    @error('shipping_address')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                    <textarea name="notes" rows="2" placeholder="Catatan untuk petani (opsional)" class="form-control mt-3">{{ old('notes') }}</textarea>
                </div>
            </section>

            <section class="card">
                <div class="card-body">
                    <h2 class="h6 fw-bold text-brand-dark mb-3">Metode Pembayaran</h2>
                    <div class="row g-3">
                        @foreach (['transfer' => ['Transfer Bank', 'Upload bukti transfer setelah pesan.'], 'cod' => ['Bayar di Tempat (COD)', 'Bayar tunai saat pesanan tiba.']] as $value => [$label, $hint])
                            <div class="col-sm-6">
                                <label class="d-block h-100">
                                    <input type="radio" name="method" value="{{ $value }}" class="choice-input visually-hidden" @checked(old('method', 'transfer') === $value)>
                                    <span class="choice-box p-3 h-100">
                                        <span class="d-block small fw-semibold">{{ $label }}</span>
                                        <span class="d-block small text-secondary">{{ $hint }}</span>
                                    </span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('method')<div class="small text-danger mt-1">{{ $message }}</div>@enderror

                    <div id="transfer-info" class="mt-3">
                        <p class="small fw-semibold mb-2">Transfer ke rekening petani berikut:</p>
                        @foreach ($groups as $lines)
                            @php($farmer = $lines->first()->product->farmer)
                            <div class="border rounded p-3 mb-2 small">
                                <span class="d-block fw-semibold">{{ $farmer?->name }}</span>
                                @if ($farmer?->hasBankAccount())
                                    <span class="d-block">{{ $farmer->bank_name }} · <span class="fw-bold">{{ $farmer->bank_account_number }}</span></span>
                                    <span class="d-block text-secondary">a.n. {{ $farmer->bank_account_name }}</span>
                                @else
                                    <span class="d-block text-secondary">Petani belum mengisi rekening. Pilih COD atau hubungi petani.</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="card">
                <div class="card-body">
                    <h2 class="h6 fw-bold text-brand-dark mb-3">Rincian Pesanan</h2>
                    @foreach ($groups as $lines)
                        <div class="mb-3">
                            <p class="small fw-semibold mb-1">Dari {{ $lines->first()->product->farmer?->name }}
                                <span class="fw-normal text-secondary">· ongkir Rp {{ number_format($shipping, 0, ',', '.') }}</span></p>
                            <ul class="list-group list-group-flush small">
                                @foreach ($lines as $l)
                                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                        <span>{{ $l->product->name }} × {{ $l->quantity }} {{ $l->product->unit }}</span>
                                        <span>Rp {{ number_format($l->product->price * $l->quantity, 0, ',', '.') }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                    @if ($groups->count() > 1)
                        <div class="alert alert-warning py-2 small mb-0">
                            Produk dari {{ $groups->count() }} petani berbeda akan dipecah menjadi {{ $groups->count() }} pesanan.
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <div class="col-lg-4">
            <aside class="card">
                <div class="card-body">
                    <h2 class="h6 fw-bold text-brand-dark">Total</h2>
                    <dl class="small mt-3 mb-3">
                        <div class="d-flex justify-content-between"><dt class="fw-normal text-secondary">Subtotal</dt><dd class="mb-1">Rp {{ number_format($subtotal, 0, ',', '.') }}</dd></div>
                        <div class="d-flex justify-content-between"><dt class="fw-normal text-secondary">Ongkir ({{ $groups->count() }}×)</dt><dd class="mb-1">Rp {{ number_format($shipping * $groups->count(), 0, ',', '.') }}</dd></div>
                        <div class="d-flex justify-content-between border-top pt-2 fs-6 fw-bold text-brand-dark"><dt>Total</dt><dd class="mb-0">Rp {{ number_format($grand, 0, ',', '.') }}</dd></div>
                    </dl>
                    <button class="btn btn-primary w-100 fw-semibold">Buat Pesanan</button>
                    <a href="{{ route('pembeli.cart.index') }}" class="d-block text-center small text-secondary mt-2">Kembali ke keranjang</a>
                </div>
            </aside>
        </div>
    </form>
@endsection
