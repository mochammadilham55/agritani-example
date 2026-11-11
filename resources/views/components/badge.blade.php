@props(['status', 'label' => null])

@php
    $key = strtolower((string) $status);

    // [background, text] sesuai palet status AgriTani.
    $gray = ['#ECEFEA', '#3F4A40'];
    $amber = ['#FCEFD6', '#7A4B00'];
    $blue = ['#DCEBFA', '#12446E'];
    $green = ['#DDF0DA', '#1F5A22'];
    $red = ['#FBE1DE', '#8E1F14'];

    [$bg, $fg] = match ($key) {
        'pending', 'menunggu', 'nonaktif' => $gray,
        'diproses', 'disiapkan' => $amber,
        'dikirim' => $blue,
        'selesai', 'aktif', 'dikonfirmasi', 'tiba', 'disetujui' => $green,
        'dibatalkan', 'ditangguhkan', 'ditolak' => $red,
        default => $gray,
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold']) }}
      style="background: {{ $bg }}; color: {{ $fg }};">{{ $label ?? ucfirst($key) }}</span>
