<?php

namespace App\Http\Controllers;

use App\Models\Recommendation;
use App\Services\RecommendationService;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function __construct(private RecommendationService $service)
    {
        $this->middleware('role:ceo,commercial_director');
    }

    public function index(Request $request)
    {
        $filter = $request->get('type', 'all');
        $status = $request->get('status', 'active');

        $query = Recommendation::with(['department.branch', 'kpi'])
            ->orderBy('created_at', 'desc');

        if ($filter !== 'all') {
            $query->where('type', $filter);
        }

        if ($status === 'active') {
            $query->where('is_dismissed', false);
        } elseif ($status === 'dismissed') {
            $query->where('is_dismissed', true);
        }

        $recommendations = $query->paginate(20)->withQueryString();

        $counts = [
            'active'    => Recommendation::where('is_dismissed', false)->count(),
            'dismissed' => Recommendation::where('is_dismissed', true)->count(),
            'critical'  => Recommendation::where('is_dismissed', false)->where('type', 'critical_lag')->count(),
            'missing'   => Recommendation::where('is_dismissed', false)->where('type', 'missing_fact')->count(),
            'drop'      => Recommendation::where('is_dismissed', false)->where('type', 'department_drop')->count(),
        ];

        return view('recommendations.index', compact('recommendations', 'counts', 'filter', 'status'));
    }

    public function dismiss(Recommendation $recommendation)
    {
        $this->service->dismiss($recommendation, auth()->id());
        return back()->with('success', 'Рекомендация принята.');
    }

    public function generate()
    {
        $this->service->generateForCurrentMonth();
        return back()->with('success', 'Рекомендации обновлены.');
    }
}
