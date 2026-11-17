@props(['label', 'value', 'hint' => null])

<div class="card h-100">
    <div class="card-body">
        <p class="small text-secondary mb-1">{{ $label }}</p>
        <p class="fs-3 fw-bold text-brand-dark mb-0">{{ $value }}</p>
        @if ($hint)<p class="small text-secondary mb-0 mt-1">{{ $hint }}</p>@endif
    </div>
</div>
