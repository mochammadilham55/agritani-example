@props(['status', 'label' => null])

@php
    $key = strtolower((string) $status);

    // [background, text] sesuai palet status Tanimart.
    $gray = ['#ECEFEA', '#3F4A40'];
    $amber = ['#FCEFD6', '#7A4B00'];
    $blue = ['#DCEBFA', '#12446E'];
    $green = ['#DDF0DA', '#1F5A22'];
    $red = ['#FBE1DE', '#8E1F14'];

    [$bg, $fg] = match ($key) {
        'pending', 'menunggu', 'nonaktif' => $gray,
        'diproses' => $amber,
        'dikirim' => $blue,
        'selesai', 'aktif', 'dikonfirmasi' => $green,
        'dibatalkan', 'ditolak' => $red,
        default => $gray,
    };
@endphp

<span {{ $attributes->merge(['class' => 'badge rounded-pill fw-semibold']) }}
      style="background: {{ $bg }}; color: {{ $fg }};">{{ $label ?? ucfirst($key) }}</span>
