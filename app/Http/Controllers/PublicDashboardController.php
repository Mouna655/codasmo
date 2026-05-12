<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\DailyProduction;
use App\Models\LoadingSnapshot;
use App\Models\LoadingRecord;
use App\Models\PoaSnapshot;
use App\Models\PoaRecord;
use App\Models\ThirdPartyCoal;

class PublicDashboardController extends Controller
{
    /** Halaman utama — landing page pemilihan dashboard */
    public function landing()
    {
        // Ambil data ringkasan terbaru untuk preview card
        $latestDate = DailyProduction::max('report_date');
        $summary    = null;

        if ($latestDate) {
            $dc = new DashboardController();
            $summary = $dc->build(Carbon::parse($latestDate));
        }

        // Preview untuk card Weekly/Loading
        $loadingPreview = $this->getLoadingPreview();

        // Preview untuk card POA
        $poaPreview = $this->getPoaPreview();

        // Preview untuk card 3rd Party
        $thirdPartyPreview = $this->getThirdPartyPreview(); 

        return view('public.landing', compact('summary', 'latestDate', 'loadingPreview', 'poaPreview', 'thirdPartyPreview'));
    }

    

private function getThirdPartyPreview(): array
{
    $empty = [
        'has_data'       => false,
        'last_update'    => '-',
        'ytd_achieve'    => 0,
        'total_plan'     => 0,
        'total_actual'   => 0,
        'sum_actual_k'   => '0',
        'by_quality'     => [],
        'by_shipper'     => [],
        'active_shippers'=> 0,
        'top_quality'    => null,
    ];

    // ── Ambil data terbaru ─────────────────────────────────
    // Untuk ThirdPartyCoal, field `year` dan `month` disimpan terpisah.
    $latestRecord = ThirdPartyCoal::whereNotNull('actual')
                        ->where('actual', '>', 0)
                        ->orderByDesc('year')
                        ->orderByDesc('month')
                        ->first();

    if (!$latestRecord) return $empty;

    $dataYear    = $latestRecord->year;
    $latestMonth = $latestRecord->month;
    $latestDate  = Carbon::create($dataYear, $latestMonth, 1);

    // ── Ambil semua data YTD (bulan yang punya actual > 0) ──
    $records = ThirdPartyCoal::where('year', $dataYear)
        ->where('month', '<=', $latestMonth)
        ->get();

    if ($records->isEmpty()) return $empty;

    // ── Total Plan & Actual ────────────────────────────────
    $totalPlan   = $records->sum('plan');    // ← sesuaikan jika nama beda
    $totalActual = $records->sum('actual'); // ← sesuaikan jika nama beda

    $ytdAchieve = $totalPlan > 0
        ? round($totalActual / $totalPlan * 100, 1)
        : 0;

    // Format tampilan angka
    $sumActualK = $this->formatTon($totalActual);

    // ── Per Kualitas (ICI 1-5) ─────────────────────────────
    // Kolom quality: 'kualitas', 'quality', 'ici_grade' — sesuaikan
    $byQuality = $records
        ->groupBy('quality')  // ← sesuaikan nama kolom
        ->map(function ($recs, $quality) {
            $plan   = $recs->sum('plan');
            $actual = $recs->sum('actual');
            $ach    = $plan > 0 ? round($actual / $plan * 100, 1) : 0;

            return [
                'quality' => $quality,
                'plan'    => round($plan),
                'actual'  => round($actual),
                'ach'     => $ach,
                // Warna berdasarkan ICI grade
                'color'   => match(true) {
                    str_contains($quality, '1') => '#1B2A8A',
                    str_contains($quality, '2') => '#2563EB',
                    str_contains($quality, '3') => '#059669',
                    str_contains($quality, '4') => '#D97706',
                    default                     => '#DC2626',
                },
                'ach_color' => $ach >= 90 ? '#059669' : ($ach >= 70 ? '#D97706' : '#DC2626'),
            ];
        })
        ->sortBy('quality')
        ->values()
        ->toArray();

    // ── Per Shipper — Top 5 by actual ─────────────────────
    // Kolom shipper: 'shipper' — sesuaikan jika beda
    $byShipper = $records
        ->groupBy('shipper')  // ← sesuaikan nama kolom
        ->map(function ($recs, $shipper) {
            $plan   = $recs->sum('plan');
            $actual = $recs->sum('actual');
            $ach    = $plan > 0 ? round($actual / $plan * 100, 1) : 0;
            return [
                'shipper'   => $shipper,
                'plan'      => round($plan),
                'actual'    => round($actual),
                'ach'       => $ach,
                'ach_color' => $ach >= 90 ? '#059669' : ($ach >= 70 ? '#D97706' : '#DC2626'),
            ];
        })
        ->filter(fn($s) => $s['actual'] > 0)  // hanya yang punya actual
        ->sortByDesc('actual')
        ->values()
        ->take(5)
        ->toArray();

    // ── Quality dengan achieve tertinggi ───────────────────
    $topQuality = collect($byQuality)
        ->filter(fn($q) => $q['actual'] > 0)
        ->sortByDesc('ach')
        ->first();

    // ── Last update: ambil dari upload_date di snapshot ────
    // Jika ada model snapshot terpisah, sesuaikan
    // Jika tidak ada, gunakan tanggal record terbaru
    $lastUpdate = $latestDate->format('d M Y');

    // Coba ambil dari snapshot jika ada
    // $snapshot = \App\Models\ThirdPartySnapshot::latestAvailable();
    // if ($snapshot) $lastUpdate = $snapshot->upload_date->format('d M Y');

    return [
        'has_data'        => true,
        'last_update'     => $lastUpdate,
        'data_year'       => $dataYear,
        'ytd_achieve'     => $ytdAchieve,
        'total_plan'      => round($totalPlan),
        'total_actual'    => round($totalActual),
        'sum_actual_k'    => $sumActualK,
        'by_quality'      => $byQuality,
        'by_shipper'      => $byShipper,
        'active_shippers' => count(array_unique(array_column($byShipper, 'shipper'))),
        'top_quality'     => $topQuality,
    ];
}

/**
 * Format angka ton → '35K', '1.2M', dst
 */
private function formatTon(float $val): string
{
    if ($val >= 1_000_000) return number_format($val / 1_000_000, 2) . 'M';
    if ($val >= 1_000)     return number_format($val / 1_000, 0)    . 'K';
    return number_format($val, 0);
}

        // ════════════════════════════════════════════════════
    // WEEKLY / LOADING PREVIEW
    // Data yang ditampilkan di card Weekly Dashboard
    // ════════════════════════════════════════════════════
    private function getLoadingPreview(): array
    {
        $empty = [
            'has_data'       => false,
            'month_label'    => null,
            'upload_date'    => null,
            'grand_total'    => 0,
            'boct_total'     => 0,
            'mahakam_total'  => 0,
            'kpi'            => [
                'Completed'   => 0,
                'In Progress' => 0,
                'Loading'     => 0,
                'Plan'        => 0,
            ],
            'total_shipment' => 0,
            'vessel_count'   => 0,
            'completed_pct'  => 0,
        ];

        // Ambil snapshot terbaru yang sukses
        $snapshot = LoadingSnapshot::where('status', 'success')
            ->orderByDesc('upload_date')
            ->orderByDesc('id')
            ->first();

        if (!$snapshot) return $empty;

        $records = LoadingRecord::where('snapshot_id', $snapshot->id)->get();

        if ($records->isEmpty()) return $empty;

        $boct    = $records->where('load_port', 'BoCT');
        $mahakam = $records->whereIn('load_port', ['Muara Berau', 'GPK Port']);

        $grandTotal = round($records->sum('total_tonnage'));

        // KPI per status
        $kpi = [];
        foreach (['Completed', 'In Progress', 'Loading', 'Plan'] as $s) {
            $kpi[$s] = round($records->where('status', $s)->sum('total_tonnage'));
        }

        // % completed dari total
        $completedPct = $grandTotal > 0
            ? round($kpi['Completed'] / $grandTotal * 100)
            : 0;

        return [
            'has_data'       => true,
            'month_label'    => $snapshot->data_month_label,
            'upload_date'    => $snapshot->upload_date->format('d M Y'),
            'grand_total'    => $grandTotal,
            'boct_total'     => round($boct->sum('total_tonnage')),
            'mahakam_total'  => round($mahakam->sum('total_tonnage')),
            'kpi'            => $kpi,
            'total_shipment' => $records->count(),
            'vessel_count'   => $records->where('shipment_type', 'Vessel')->count(),
            'completed_pct'  => $completedPct,
        ];
    }

    // ════════════════════════════════════════════════════
    // POA PREVIEW
    // Data yang ditampilkan di card Previous Outlook Dashboard
    // ════════════════════════════════════════════════════
    private function getPoaPreview(): array
    {
        $empty = [
            'has_data'    => false,
            'data_year'   => null,
            'upload_date' => null,
            'companies'   => [],
            'overall_ach' => 0,
            'total_outlook' => 0,
            'total_actual'  => 0,
        ];

        // Snapshot POA terbaru
        $snapshot = PoaSnapshot::where('status', 'success')
            ->orderByDesc('upload_date')
            ->orderByDesc('id')
            ->first();

        if (!$snapshot) return $empty;

        $companies = ['IMM', 'TCM', 'BEK', 'GPK', 'TIS'];

        // Hitung per company: sum outlook & actual per bulan yang sudah terlewati
        $currentMonth = now()->month;
        $year         = $snapshot->data_year;

        $records = PoaRecord::where('snapshot_id', $snapshot->id)
            ->where('year', $year)
            ->where('month_number', '<=', $currentMonth)
            ->get();

        $companyData = [];
        foreach ($companies as $co) {
            $coRecs  = $records->where('company', $co);
            $outlook = round($coRecs->sum('outlook'), 1);
            $actual  = round($coRecs->sum('actual'), 1);
            $ach     = $outlook > 0 ? round($actual / $outlook * 100) : 0;

            $companyData[] = [
                'company' => $co,
                'outlook' => $outlook,
                'actual'  => $actual,
                'ach'     => $ach,
            ];
        }

        $totalOutlook = round($records->sum('outlook'), 1);
        $totalActual  = round($records->sum('actual'), 1);
        $overallAch   = $totalOutlook > 0
            ? round($totalActual / $totalOutlook * 100)
            : 0;

        return [
            'has_data'       => true,
            'data_year'      => $year,
            'upload_date'    => $snapshot->upload_date->format('d M Y'),
            'companies'      => $companyData,
            'overall_ach'    => $overallAch,
            'total_outlook'  => $totalOutlook,
            'total_actual'   => $totalActual,
        ];
    }


    /** Daily dashboard publik */
    public function daily(\Illuminate\Http\Request $request)
    {
        $dc   = new DashboardController();
        $date = $request->date
            ? Carbon::parse($request->date)
            : Carbon::parse(DailyProduction::max('report_date') ?? today());

        $dashboard = $dc->build($date);

        // Ambil daftar tanggal yang punya data (untuk date picker)
        $availableDates = DailyProduction::selectRaw('DATE(report_date) as d')
            ->distinct()->orderByDesc('d')->limit(60)->pluck('d');

        return view('public.daily', compact('dashboard', 'date', 'availableDates'));
    }

    /** Weekly — placeholder */
    public function weekly()
    {
        return view('public.weekly');
    }

    /** API endpoint publik */
    public function apiDaily(\Illuminate\Http\Request $request)
    {
        $dc   = new DashboardController();
        $date = $request->date
            ? Carbon::parse($request->date)
            : Carbon::parse(DailyProduction::max('report_date') ?? today());

        return response()->json($dc->build($date));
    }
}
