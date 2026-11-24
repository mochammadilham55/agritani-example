{{--
    Isi laporan penjualan yang dipakai petani dan admin.
    Variabel: $route (nama route laporan), $rows, $from, $to, $status, $summary, $topProducts.
--}}
<form method="GET" action="{{ route($route) }}" class="card mb-4 no-print">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-sm-6 col-lg-3">
                <label for="dari" class="form-label small fw-medium">Dari tanggal</label>
                <input type="date" id="dari" name="dari" value="{{ $from->format('Y-m-d') }}" class="form-control">
                @error('dari')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6 col-lg-3">
                <label for="sampai" class="form-label small fw-medium">Sampai tanggal</label>
                <input type="date" id="sampai" name="sampai" value="{{ $to->format('Y-m-d') }}" class="form-control">
                @error('sampai')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6 col-lg-3">
                <label for="status" class="form-label small fw-medium">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\Order::STATUSES as $s)
                        <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                @error('status')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6 col-lg-3 d-flex gap-2">
                <button class="btn btn-primary fw-semibold flex-grow-1">Tampilkan</button>
                <a href="{{ route($route) }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </div>
    </div>
</form>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <p class="mb-0 small text-secondary">Periode <strong>{{ $from->format('d M Y') }}</strong> – <strong>{{ $to->format('d M Y') }}</strong></p>
    <div class="d-flex gap-2 no-print">
        <a href="{{ route($route, array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-outline-secondary btn-sm">Unduh CSV</a>
        <button type="button" class="btn btn-outline-secondary btn-sm" data-print>Cetak</button>
    </div>
</div>

<div class="row g-3">
    <div class="col-sm-6 col-xl-3"><x-stat-card label="Jumlah Pesanan" :value="$summary->count" /></div>
    <div class="col-sm-6 col-xl-3"><x-stat-card label="Pesanan Selesai" :value="$summary->completed" /></div>
    <div class="col-sm-6 col-xl-3"><x-stat-card label="Dibatalkan" :value="$summary->cancelled" /></div>
    <div class="col-sm-6 col-xl-3"><x-stat-card label="Pendapatan" :value="'Rp '.number_format($summary->revenue, 0, ',', '.')" hint="Dari pesanan selesai" /></div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="h6 fw-bold text-brand-dark mb-0">Rincian Pesanan</h2>
            <input type="search" data-filter-table="#tabel-laporan" placeholder="Cari di tabel…" class="form-control form-control-sm no-print" style="width: 12rem;" aria-label="Cari di tabel">
        </div>
        <div class="card">
            <div class="table-responsive">
                <table id="tabel-laporan" class="table table-hover align-middle mb-0 small">
                    <thead>
                        <tr><th>Pesanan</th><th>Produk</th><th>Status</th><th class="text-end">Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td class="fw-medium">#{{ $row->order->id }}<span class="d-block small fw-normal text-secondary">{{ $row->order->buyer->name }} · {{ $row->order->created_at->format('d M Y') }}</span></td>
                                <td class="text-secondary">{{ $row->items->map(fn ($i) => $i->product->name.' ×'.$i->quantity)->join(', ') }}</td>
                                <td><x-badge :status="$row->order->status" /></td>
                                <td class="text-end">Rp {{ number_format($row->amount, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-5 text-center text-secondary">Tidak ada pesanan pada periode ini. Ubah rentang tanggal atau status lalu tekan Tampilkan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <h2 class="h6 fw-bold text-brand-dark mb-3">Produk Terlaris</h2>
        <div class="card">
            <ul class="list-group list-group-flush small">
                @forelse ($topProducts as $p)
                    <li class="list-group-item d-flex justify-content-between px-3 py-2">
                        <span class="fw-medium">{{ $p->name }}<span class="d-block small fw-normal text-secondary">{{ $p->qty }} terjual</span></span>
                        <span>Rp {{ number_format($p->total, 0, ',', '.') }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-center text-secondary py-4">Produk terlaris muncul setelah ada pesanan selesai pada periode ini.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
