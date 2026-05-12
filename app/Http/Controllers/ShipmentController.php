<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\{ShipmentSnapshot, ShipmentRecord};

class ShipmentController extends Controller
{
    // Warna status
    private const STATUS_COLOR = [
        'Completed'   => '#06B6D4',
        'In Progress' => '#F59E0B',
        'Loading'     => '#F97316',
        'Plan'        => '#EC4899',
    ];

    // Warna company untuk bar chart
    private const COMPANY_COLOR = [
        'EBP' => '#06B6D4',
        'BEK' => '#1B2A8A',
        'IMM' => '#8B5CF6',
        'GPK' => '#F59E0B',
        'TCM' => '#10B981',
        'JBG' => '#F97316',
    ];

    public function index(Request $request)
    {
        $availableDates = ShipmentSnapshot::availableDates();
        $selectedDate   = $request->date ?? ($availableDates[0]['date'] ?? today()->toDateString());
        $snapshot       = $this->findSnapshot($selectedDate);
        $data           = $this->buildData($snapshot);

        return view('public.shipment', compact(
            'data','availableDates','selectedDate','snapshot'
        ));
    }

    public function adminIndex(Request $request)
    {
        $availableDates = ShipmentSnapshot::availableDates();
        $selectedDate   = $request->date ?? ($availableDates[0]['date'] ?? today()->toDateString());
        $snapshot       = $this->findSnapshot($selectedDate);
        $data           = $this->buildData($snapshot);

        return view('dashboard.shipment', compact(
            'data','availableDates','selectedDate','snapshot'
        ));
    }

    public function apiData(Request $request)
    {
        $snapshot = $this->findSnapshot($request->date ?? today()->toDateString());
        return response()->json($this->buildData($snapshot));
    }

    // ════════════════════════════════════════════
    // PRIVATE HELPERS
    // ════════════════════════════════════════════

    private function findSnapshot(string $date): ?ShipmentSnapshot
    {
        return ShipmentSnapshot::where('status','success')
            ->whereDate('upload_date','<=',$date)
            ->orderByDesc('upload_date')->orderByDesc('id')->first()
            ?? ShipmentSnapshot::latestAvailable();
    }

    public function buildData(?ShipmentSnapshot $snapshot): array
    {
        if (!$snapshot) {
            return ['has_data'=>false,'snapshot'=>null,'months'=>[],'available_months'=>[]];
        }

        // Ambil semua records, group by month_number
        $allRecords = ShipmentRecord::where('snapshot_id',$snapshot->id)->get();

        // Bangun data per bulan
        $months = [];
        foreach (range(1,6) as $mn) {
            $recs = $allRecords->where('month_number',$mn);
            if ($recs->isEmpty()) continue;

            $monthLabel = $recs->first()->month_label;
            $months[$mn] = $this->buildMonthData($recs, $mn, $monthLabel);
        }

        return [
            'has_data'         => true,
            'snapshot'         => [
                'id'          => $snapshot->id,
                'upload_date' => $snapshot->upload_date->format('d M Y'),
            ],
            'months'           => $months,
            'available_months' => array_keys($months),
            'default_month'    => array_key_first($months) ?? 1,
        ];
    }

    private function buildMonthData(Collection $recs, int $mn, string $label): array
    {
        $mahakam = $recs->filter(fn($r) => in_array($r->load_port,['Muara Berau','GPK Port']));
        $boct    = $recs->filter(fn($r) => $r->load_port === 'BoCT');

        return [
            'month_number' => $mn,
            'month_label'  => $label,

            // Tabel dua panel
            'mahakam_rows' => $this->buildRows($mahakam),
            'boct_rows'    => $this->buildRows($boct),
            'mahakam_total'=> round($mahakam->sum('total_tonnage')),
            'boct_total'   => round($boct->sum('total_tonnage')),
            'grand_total'  => round($recs->sum('total_tonnage')),

            // KPI panel kanan
            'kpi' => [
                'itm'  => $this->buildKpi($recs),
                'boct' => $this->buildKpi($boct),
                'mah'  => $this->buildKpi($mahakam),
            ],

            // Chart bawah
            'chart_company' => $this->buildCompanyChart($recs),
        ];
    }

    private function buildRows(Collection $recs): array
    {
        return $recs->sortBy('no_row')->map(fn($r) => [
            'no'           => $r->no_row,
            'end_user'     => $r->end_user,
            'load_port'    => $r->load_port,
            'ts_ar'        => $r->ts_ar,
            'cv_ar'        => $r->cv_ar ? round($r->cv_ar) : null,
            'cv_nar'       => $r->cv_nar ? round($r->cv_nar) : null,
            'total'        => round($r->total_tonnage),
            'pct_shipper'  => $r->pct_shipper ? round($r->pct_shipper * 100, 1) : 0,
            'status'       => $r->status,
            'status_color' => self::STATUS_COLOR[$r->status] ?? '#94a3b8',
            'company'      => $r->company,
            'shipment_type'=> $r->shipment_type,
        ])->values()->toArray();
    }

    private function buildKpi(Collection $recs): array
    {
        $total    = round($recs->sum('total_tonnage'));
        $vessel   = $recs->where('shipment_type','Vessel')->count();
        $direct   = $recs->where('shipment_type','Direct Shipment')->count();
        $dump     = $recs->where('shipment_type','Dump Truck')->count();

        return compact('total','vessel','direct','dump');
    }

    private function buildCompanyChart(Collection $recs): array
    {
        $companies = $recs->pluck('company')->unique()->sort()->values()->toArray();
        $data      = [];
        $colors    = [];

        foreach ($companies as $co) {
            $data[]   = round($recs->where('company',$co)->sum('total_tonnage'));
            $colors[] = self::COMPANY_COLOR[$co] ?? '#94a3b8';
        }

        return ['labels'=>$companies,'data'=>$data,'colors'=>$colors];
    }
}