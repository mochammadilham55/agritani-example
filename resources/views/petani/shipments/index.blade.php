@extends('layouts.app')

@section('title', 'Pengiriman')
@section('heading', 'Pengiriman')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
    @endif

    <div class="d-grid gap-3">
        @forelse ($orders as $o)
            <section class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                            <a href="{{ route('petani.orders.show', $o->id) }}" class="fw-semibold text-decoration-none">Pesanan #{{ $o->id }}</a>
                            <span class="ms-2 small text-secondary">{{ $o->buyer->name }}</span>
                        </div>
                        <x-badge :status="$o->status" />
                    </div>
                    <p class="small text-secondary mt-1 mb-0">{{ $o->items->map(fn ($i) => $i->product->name.' ×'.$i->quantity)->join(', ') }}</p>
                    <p class="small text-secondary mb-0">{{ $o->shipping_address }}</p>

                    <form method="POST" action="{{ route('petani.shipments.update', $o->id) }}" class="row g-2 mt-2">
                        @csrf @method('PUT')
                        <div class="col-sm-4"><input name="courier" value="{{ old('courier', $o->courier) }}" placeholder="Kurir (JNE, J&T, …)" required class="form-control" aria-label="Kurir"></div>
                        <div class="col-sm-4"><input name="tracking_number" value="{{ old('tracking_number', $o->tracking_number) }}" placeholder="No. resi" required class="form-control" aria-label="No. resi"></div>
                        <div class="col-sm-4"><button class="btn btn-primary w-100 fw-semibold">{{ $o->status === 'diproses' ? 'Kirim Pesanan' : 'Perbarui' }}</button></div>
                    </form>
                </div>
            </section>
        @empty
            <div class="card text-center text-secondary py-5" style="border-style: dashed;"><div class="card-body">Tidak ada pesanan yang perlu dikirim.</div></div>
        @endforelse
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
