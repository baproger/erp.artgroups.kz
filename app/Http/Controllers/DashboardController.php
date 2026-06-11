<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use App\Services\KpiService;
use App\Services\RecommendationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function __construct(
        private KpiService $kpiService,
        private RecommendationService $recService,
    ) {}

    public function liveStats(Request $request)
    {
        /** @var User $user */
        $user  = auth()->user();
        $now   = Carbon::now();
        $year  = (int) $request->get('year',  $now->year);
        $month = (int) $request->get('month', $now->month);

        $companyStats = $this->kpiService->getCompanyStats($year, $month);
        $trend        = $this->kpiService->getCompanyDailyTrend($year, $month);

        $deptRows = collect($companyStats['departments'])->map(fn($d) => [
            'name'        => $d['department']->name,
            'eff_pct'     => $d['eff_pct'],
            'problematic' => $d['problematic'],
        ]);

        return response()->json([
            'eff_pct'     => $companyStats['eff_pct'],
            'problematic' => $deptRows->sum('problematic'),
            'trend'       => $trend,
            'departments' => $deptRows,
            'updated_at'  => $now->format('H:i:s'),
        ]);
    }

    public function index(Request $request)
    {
        /** @var User $user */
        $user  = auth()->user();
        $now   = Carbon::now();
        $year  = (int) $request->get('year',  $now->year);
        $month = (int) $request->get('month', $now->month);

        // Auto-generate recommendations once per hour
        $cacheKey = "rec_generated_{$year}_{$month}";
        if (! Cache::has($cacheKey)) {
            $this->recService->generateForCurrentMonth();
            Cache::put($cacheKey, true, now()->addHour());
        }

        // CEO → full company overview
        if ($user->canSeeAllBranches()) {
            $branchesStats   = $this->kpiService->getAllBranchesStats($year, $month);
            $companyStats    = $this->kpiService->getCompanyStats($year, $month);
            $trend           = $this->kpiService->getCompanyDailyTrend($year, $month);
            $recommendations = $this->recService->getActive();

            return view('dashboard.overview', compact(
                'branchesStats', 'companyStats', 'trend', 'recommendations', 'year', 'month'
            ));
        }

        $accessibleIds = $user->accessibleBranchIds();

        // Multi-branch user → show overview limited to accessible branches
        if (count($accessibleIds) > 1) {
            $branches      = Branch::whereIn('id', $accessibleIds)->orderBy('sort_order')->get();
            $branchesStats = $branches->map(
                fn($b) => $this->kpiService->getBranchStats($b, $year, $month)
            )->values()->toArray();

            $companyStats = $this->kpiService->getCompanyStats($year, $month);
            $trend        = $this->kpiService->getCompanyDailyTrend($year, $month);
            $recommendations = $this->recService->getActive();

            return view('dashboard.overview', compact(
                'branchesStats', 'companyStats', 'trend', 'recommendations', 'year', 'month'
            ));
        }

        // Single-branch user with dept-management rights → branch dashboard
        if ($user->canSeeAllDepartments() && ! empty($accessibleIds)) {
            return redirect()->route('branch.view', $accessibleIds[0]);
        }

        // Staff → their department
        $department = $user->department;
        if (! $department) {
            return redirect()->route('profile')->with('error', 'Отдел не назначен. Обратитесь к администратору.');
        }

        $deptStats = $this->kpiService->getDepartmentStats($department, $year, $month);

        return view('dashboard.department', compact('deptStats', 'year', 'month'));
    }
}
