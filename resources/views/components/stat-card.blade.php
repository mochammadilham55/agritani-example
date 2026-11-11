@props(['label', 'value', 'hint' => null])

<div class="rounded-xl border border-line bg-white p-5">
    <p class="text-sm text-gray-500">{{ $label }}</p>
    <p class="mt-1 text-2xl font-extrabold text-brand-dark">{{ $value }}</p>
    @if ($hint)<p class="mt-1 text-xs text-gray-400">{{ $hint }}</p>@endif
</div>
