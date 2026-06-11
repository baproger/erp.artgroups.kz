<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use App\Services\KpiService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function __construct(private KpiService $kpiService) {}

    public function show(Branch $branch, Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user->canAccessBranch($branch)) {
            abort(403);
        }

        $now   = Carbon::now();
        $year  = (int) $request->get('year',  $now->year);
        $month = (int) $request->get('month', $now->month);

        $branchStats = $this->kpiService->getBranchStats($branch, $year, $month);
        $trend       = $this->kpiService->getBranchDailyTrend($branch, $year, $month);

        return view('dashboard.branch', compact('branchStats', 'trend', 'year', 'month'));
    }
}
