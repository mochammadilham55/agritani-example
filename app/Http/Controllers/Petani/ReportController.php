<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Concerns\BuildsSalesReport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/** Anggota 4 — laporan penjualan milik petani yang sedang login. */
class ReportController extends Controller
{
    use BuildsSalesReport;

    public function index(Request $request)
    {
        $report = $this->salesReport($request, auth()->id());

        if ($request->query('export') === 'csv') {
            return $this->salesCsv($report, 'laporan-penjualan-'.now()->format('Ymd').'.csv');
        }

        return view('petani.reports.index', $report);
    }
}
