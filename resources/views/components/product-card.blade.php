@props(['product'])

<a href="{{ route('products.show', $product) }}"
   class="group flex flex-col overflow-hidden rounded-xl border border-line bg-white transition hover:shadow-md">
    <div class="flex aspect-[4/3] items-center justify-center bg-role-bg text-5xl">
        @if ($product->image)
            <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover" loading="lazy">
        @else
            <span aria-hidden="true">🌿</span>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-4">
        <p class="text-xs text-gray-400">{{ $product->category?->name }}</p>
        <h3 class="font-semibold group-hover:text-brand">{{ $product->name }}</h3>
        <p class="mt-1 text-lg font-extrabold text-brand-dark">
            Rp {{ number_format($product->price, 0, ',', '.') }}<span class="text-xs font-normal text-gray-500"> / {{ $product->unit }}</span>
        </p>
        <div class="mt-auto flex items-center justify-between pt-3 text-xs text-gray-500">
            <span>{{ $product->farmer?->name }}</span>
            @if ($product->stock < 1)
                <x-badge status="dibatalkan" label="Habis" />
            @elseif (isset($product->avg_rating) && $product->avg_rating)
                <span>⭐ {{ number_format($product->avg_rating, 1) }}</span>
            @endif
        </div>
    </div>
</a>
