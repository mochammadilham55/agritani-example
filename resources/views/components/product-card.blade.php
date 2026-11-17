@props(['product'])

<a href="{{ route('products.show', $product) }}" class="card product-card h-100 overflow-hidden">
    <div class="ratio ratio-4x3 product-thumb">
        @if ($product->image)
            <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="object-fit-cover w-100 h-100" loading="lazy">
        @else
            <div class="d-flex align-items-center justify-content-center" aria-hidden="true">🌿</div>
        @endif
    </div>
    <div class="card-body d-flex flex-column p-3">
        <p class="small text-secondary mb-0">{{ $product->category?->name }}</p>
        <h3 class="h6 fw-semibold product-name mb-1">{{ $product->name }}</h3>
        <p class="fs-5 fw-bold text-brand-dark mb-0">
            Rp {{ number_format($product->price, 0, ',', '.') }}<span class="small fw-normal text-secondary"> / {{ $product->unit }}</span>
        </p>
        <div class="mt-auto d-flex align-items-center justify-content-between pt-2 small text-secondary">
            <span>{{ $product->farmer?->name }}</span>
            @if ($product->stock < 1)
                <x-badge status="dibatalkan" label="Habis" />
            @endif
        </div>
    </div>
</a>
