<?php

namespace App\Http\Controllers\Concerns;

use Carbon\Carbon;
use Illuminate\Http\Request;

trait HandlesReports
{
    /** @return array{0: Carbon, 1: Carbon} */
    protected function range(Request $request): array
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $to = $request->filled('to') ? Carbon::parse($request->query('to')) : today();
        $from = $request->filled('from') ? Carbon::parse($request->query('from')) : $to->copy()->subDays(29);

        // Batasi rentang agar grafik per hari tetap ringan.
        if ($from->diffInDays($to) > 366) {
            $from = $to->copy()->subDays(366);
        }

        return [$from->startOfDay(), $to->startOfDay()];
    }

    /** Cegah CSV injection: sel yang diawali = + - @ diberi tanda petik. */
    protected function safe(?string $value): string
    {
        $value = (string) $value;

        return $value !== '' && str_contains('=+-@', $value[0]) ? "'".$value : $value;
    }
}
