<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ThirdPartyCoal;

class ThirdPartyController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) ($request->year ?? now()->year);
        $data = $this->build($year);
        return view('public.third-party', compact('data', 'year'));
    }

    public function apiData(Request $request)
    {
        $year = (int) ($request->year ?? now()->year);
        return response()->json($this->build($year));
    }

    public function dashboard(Request $request)
    {
        $year = (int) ($request->year ?? now()->year);
        $data = $this->build($year);
        return view('dashboard.third-party', compact('data', 'year'));
    }

    public function apiDashboardData(Request $request)
    {
        return $this->apiData($request);
    }

    public function build(int $year): array
    {
        $rows = ThirdPartyCoal::forYear($year)->get();

        if ($rows->isEmpty()) {
            return $this->emptyData($year);
        }

        // ── KPI: Total YTD ──────────────────────────────────────
        $totalPlan   = $rows->sum('plan');
        $totalActual = $rows->sum('actual');
        $ytdAchieve  = $totalPlan > 0
            ? round($totalActual / $totalPlan * 100, 1)
            : 0;

        // ── By Shipper ──────────────────────────────────────────
        $byShipper = $rows
            ->groupBy('shipper')
            ->map(fn($g) => [
                'shipper' => $g->first()->shipper,
                'plan'    => $g->sum('plan'),
                'actual'  => $g->sum('actual'),
                'achieve' => $g->sum('plan') > 0
                    ? round($g->sum('actual') / $g->sum('plan') * 100, 1)
                    : 0,
            ])
            ->filter(fn($v) => $v['plan'] > 0 || $v['actual'] > 0)
            ->sortByDesc('achieve')
            ->values();

        // ── By Quality (donut) ──────────────────────────────────
        $byQuality = $rows
            ->groupBy('quality')
            ->map(fn($g) => [
                'quality' => $g->first()->quality,
                'actual'  => $g->sum('actual'),
                'plan'    => $g->sum('plan'),
                // Detail per shipper dalam quality ini (untuk tooltip interaktif)
                'shippers' => $g->groupBy('shipper')
                    ->map(fn($sg) => [
                        'shipper' => $sg->first()->shipper,
                        'actual'  => $sg->sum('actual'),
                        'plan'    => $sg->sum('plan'),
                    ])
                    ->filter(fn($v) => $v['actual'] > 0)
                    ->sortByDesc('actual')
                    ->values(),
            ])
            ->filter(fn($v) => $v['actual'] > 0)
            ->sortBy('quality')
            ->values();

        // ── By Month (bar + line chart) ─────────────────────────
        $byMonth = collect(range(1, 12))->map(function ($m) use ($rows) {
            $monthRows = $rows->where('month', $m);
            $plan      = $monthRows->sum('plan');
            $actual    = $monthRows->sum('actual');
            return [
                'month'      => $m,
                'month_name' => \Carbon\Carbon::create()->month($m)->format('M'),
                'plan'       => $plan,
                'actual'     => $actual,
                'achieve'    => $plan > 0 ? round($actual / $plan * 100, 1) : 0,
            ];
        });

        // Last upload date
        $lastUpload = ThirdPartyCoal::forYear($year)
            ->latest('updated_at')
            ->value('updated_at');

        return [
            'year'           => $year,
            'last_update'    => $lastUpload
                ? \Carbon\Carbon::parse($lastUpload)->format('d M Y')
                : '—',
            'kpi' => [
                'ytd_achieve'  => $ytdAchieve,
                'sum_actual'   => $totalActual,
                'sum_actual_k' => $this->formatK($totalActual),
                'total_plan'   => $totalPlan,
            ],
            'by_shipper'     => $byShipper->values(),
            'by_quality'     => $byQuality->values(),
            'by_month'       => $byMonth->values(),
        ];
    }

    /* ── Helpers ──────────────────────────────────────────────── */
    private function formatK(float $v): string
    {
        if ($v >= 1_000_000) return number_format($v / 1_000_000, 1) . 'M';
        if ($v >= 1_000)     return number_format($v / 1_000, 1) . 'K';
        return number_format($v, 0);
    }

    private function emptyData(int $year): array
    {
        return [
            'year'        => $year,
            'last_update' => '—',
            'kpi'         => ['ytd_achieve' => 0, 'sum_actual' => 0, 'sum_actual_k' => '0', 'total_plan' => 0],
            'by_shipper'  => [],
            'by_quality'  => [],
            'by_month'    => collect(range(1, 12))->map(fn($m) => [
                'month'      => $m,
                'month_name' => \Carbon\Carbon::create()->month($m)->format('M'),
                'plan' => 0, 'actual' => 0, 'achieve' => 0,
            ])->values(),
        ];
    }
}