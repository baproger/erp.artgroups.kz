<?php

namespace App\Http\Controllers;

use App\Exports\KpiReportExport;
use App\Services\KpiService;
use App\Services\RecommendationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function __construct(
        private KpiService $kpiService,
        private RecommendationService $recService,
    ) {
        $this->middleware('role:ceo,commercial_director');
    }

    public function download(Request $request)
    {
        $now   = Carbon::now();
        $year  = (int) $request->get('year', $now->year);
        $month = (int) $request->get('month', $now->month);

        $filename = "kpi_report_{$year}_{$month}.xlsx";

        return Excel::download(
            new KpiReportExport($year, $month, $this->kpiService, $this->recService),
            $filename
        );
    }
}
