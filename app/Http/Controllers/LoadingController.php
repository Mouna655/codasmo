<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\{LoadingSnapshot, LoadingRecord};

class LoadingController extends Controller
{
    private const STATUS_COLORS = [
        'Completed'   => '#06B6D4',
        'In Progress' => '#F59E0B',
        'Loading'     => '#F97316',
        'Plan'        => '#EC4899',
    ];

    public function index(Request $request)
    {
        $availableDates = LoadingSnapshot::availableDates();
        $selectedDate   = $request->date ?? ($availableDates[0]['date'] ?? today()->toDateString());
        $snapshot       = $this->findSnapshot($selectedDate);
        $data           = $this->buildData($snapshot);

        return view('public.loading', compact('data','availableDates','selectedDate','snapshot'));
    }

    public function adminIndex(Request $request)
    {
        $availableDates = LoadingSnapshot::availableDates();
        $selectedDate   = $request->date ?? ($availableDates[0]['date'] ?? today()->toDateString());
        $snapshot       = $this->findSnapshot($selectedDate);
        $data           = $this->buildData($snapshot);

        return view('dashboard.loading', compact('data','availableDates','selectedDate','snapshot'));
    }

    public function apiData(Request $request)
    {
        $snapshot = $this->findSnapshot($request->date ?? today()->toDateString());
        return response()->json($this->buildData($snapshot));
    }

    // ════════════════════════════════════════════════════
    // BUILDER
    // ════════════════════════════════════════════════════

    private function findSnapshot(string $date): ?LoadingSnapshot
    {
        return LoadingSnapshot::where('status','success')
            ->whereDate('upload_date','<=',$date)
            ->orderByDesc('upload_date')->orderByDesc('id')
            ->first()
            ?? LoadingSnapshot::latestAvailable();
    }

    public function buildData(?LoadingSnapshot $snapshot): array
    {
        if (!$snapshot) {
            return ['has_data'=>false,'snapshot'=>null,'overall'=>$this->empty(),'boct'=>$this->empty(),'mahakam'=>$this->empty()];
        }

        $records = LoadingRecord::where('snapshot_id',$snapshot->id)->get();
        $boct    = $records->filter(fn($r) => $r->load_port === 'BoCT');
        $mahakam = $records->filter(fn($r) => in_array($r->load_port,['Muara Berau','GPK Port']));

        return [
            'has_data'       => true,
            'snapshot'       => [
                'id'             => $snapshot->id,
                'upload_date'    => $snapshot->upload_date->format('d M Y'),
                'month_label'    => $snapshot->data_month_label,
                'pen_week_label' => $snapshot->pen_week_label,
                'dem_week_label' => $snapshot->dem_week_label,
            ],
            'pen_week_label' => $snapshot->pen_week_label,
            'dem_week_label' => $snapshot->dem_week_label,

            // Overall → dua tabel terpisah (BoCT + Mahakam)
            'overall' => [
                'boct_rows'    => $this->buildRows($boct, 'overall'),
                'mahakam_rows' => $this->buildRows($mahakam, 'overall'),
                'boct_total'   => round($boct->sum('total_tonnage')),
                'mahakam_total'=> round($mahakam->sum('total_tonnage')),
                'grand_total'  => round($records->sum('total_tonnage')),
                'kpi'          => $this->buildKpi($records),
                'chart_company'=> $this->buildCompanyChart($records),
                'chart_count'  => $this->buildCountChart($records),
                'chart_port'   => $this->buildPortPie($records),
            ],

            // BoCT tab — tabel penuh + pen/dem kolom + weight avg
            'boct' => [
                'rows'          => $this->buildRows($boct, 'boct', $snapshot->pen_week_label, $snapshot->dem_week_label),
                'total'         => round($boct->sum('total_tonnage')),
                'kpi'           => $this->buildKpi($boct),
                'chart_company' => $this->buildCompanyChart($boct),
                'chart_count'   => $this->buildCountChart($boct),
                'chart_product' => $this->buildProductPie($boct),
                'weight_pen'    => $this->weightedAvg($boct),
                'weight_dem'    => $this->weightedAvgDem($boct),
            ],

            // Mahakam tab
            'mahakam' => [
                'rows'          => $this->buildRows($mahakam, 'mahakam', $snapshot->pen_week_label, $snapshot->dem_week_label),
                'total'         => round($mahakam->sum('total_tonnage')),
                'kpi'           => $this->buildKpi($mahakam),
                'chart_company' => $this->buildCompanyChart($mahakam),
                'chart_count'   => $this->buildCountChart($mahakam),
                'chart_port'    => $this->buildPortPie($mahakam),
                'weight_pen'    => $this->weightedAvg($mahakam),
                'weight_dem'    => $this->weightedAvgDem($mahakam),
            ],
        ];
    }

    // ════════════════════════════════════════════════════
    // ROW BUILDER
    // ════════════════════════════════════════════════════

    private function buildRows(Collection $recs, string $tab, string $penLabel='', string $demLabel=''): array
    {
        return $recs->map(function($r) use ($tab, $penLabel, $demLabel) {
            $base = [
                'no'           => $r->no_row,
                'load_port'    => $r->load_port,
                'end_user'     => $r->end_user,
                'total'        => round($r->total_tonnage),
                'pct_shipper'  => $r->pct_shipper ? round($r->pct_shipper * 100, 1) : 0,
                'status'       => $r->status,
                'status_color' => self::STATUS_COLORS[$r->status] ?? '#94a3b8',
                'company'      => $r->company,
                'shipment_type'=> $r->shipment_type,
            ];

            if ($tab === 'overall') {
                $base['ts_ar'] = $r->ts_ar;
                $base['cv_ar'] = $r->cv_ar ? round($r->cv_ar) : null;
            }

            if (in_array($tab, ['boct','mahakam'])) {
                $base['eta']   = $r->eta;
                $base['etb']   = $r->etb;
                $base['etd']   = $r->etd;
                $base['lay']   = $r->lay;
                $base['can']   = $r->can;
                $base['pen_w'] = $r->pen_value;
                $base['dem_w'] = $r->dem_value;
            }

            return $base;
        })->values()->toArray();
    }

    // ════════════════════════════════════════════════════
    // KPI & CHARTS
    // ════════════════════════════════════════════════════

    private function buildKpi(Collection $recs): array
    {
        return collect(['Completed','In Progress','Loading','Plan'])
            ->mapWithKeys(fn($s) => [$s => round($recs->where('status',$s)->sum('total_tonnage'))])
            ->toArray();
    }

    private function buildCompanyChart(Collection $recs): array
    {
        $companies = ['EBP','IMM','BEK','GPK'];
        $statuses  = ['Completed','In Progress','Loading','Plan'];
        $colors    = ['Completed'=>'#06B6D4','In Progress'=>'#F59E0B','Loading'=>'#F97316','Plan'=>'#EC4899'];

        $datasets = [];
        foreach ($statuses as $s) {
            $data = array_map(fn($c) => round($recs->where('company',$c)->where('status',$s)->sum('total_tonnage')), $companies);
            if (array_sum($data) > 0) {
                $datasets[] = ['label'=>$s,'data'=>$data,'color'=>$colors[$s]];
            }
        }
        return ['labels'=>$companies,'datasets'=>$datasets];
    }

    private function buildCountChart(Collection $recs): array
    {
        $companies = $recs->pluck('company')->unique()->sort()->values()->toArray();
        $types     = ['Direct Shipment','Dump Truck','Vessel'];
        $colors    = ['Direct Shipment'=>'#06B6D4','Dump Truck'=>'#F97316','Vessel'=>'#1B2A8A'];

        $datasets = [];
        foreach ($types as $t) {
            $data = array_map(fn($c) => $recs->where('company',$c)->where('shipment_type',$t)->count(), $companies);
            if (array_sum($data) > 0) {
                $datasets[] = ['label'=>$t,'data'=>$data,'color'=>$colors[$t]];
            }
        }
        return ['labels'=>$companies,'datasets'=>$datasets];
    }

    private function buildPortPie(Collection $recs): array
    {
        $portColors = ['BoCT'=>'#1B2A8A','Muara Berau'=>'#06B6D4','GPK Port'=>'#F59E0B'];
        $groups = $recs->groupBy('load_port')->map(fn($g,$k) => [
            'label' => $k,
            'value' => round($g->sum('total_tonnage')),
            'color' => $portColors[$k] ?? '#94a3b8',
        ])->values()->toArray();

        return [
            'labels' => array_column($groups,'label'),
            'data'   => array_column($groups,'value'),
            'colors' => array_column($groups,'color'),
        ];
    }

    private function buildProductPie(Collection $recs): array
    {
        $productMap = [
            'EB.HS'=>'t_imm_eb_hs','EB.LS'=>'t_imm_eb_ls','EB.MS'=>'t_imm_eb_ms',
            'WB.LS'=>'t_imm_wb_ls','WB.HS'=>'t_imm_wb_hs',
            'TCM.LS'=>'t_tcm_ls','TCM.HS'=>'t_tcm_hs','TCM.MS'=>'t_tcm_ms',
            'BEK.LS'=>'t_bek_ls','BEK.HS'=>'t_bek_hs',
            'JBG'=>'t_jbg','GPK'=>'t_gpk','TIS'=>'t_tis',
        ];
        $colors = [
            'EB.HS'=>'#1B2A8A','EB.LS'=>'#2563EB','EB.MS'=>'#7C3AED',
            'WB.LS'=>'#0891B2','WB.HS'=>'#06B6D4',
            'TCM.LS'=>'#059669','TCM.HS'=>'#10B981','TCM.MS'=>'#34D399',
            'BEK.LS'=>'#DC2626','BEK.HS'=>'#F87171',
            'JBG'=>'#D97706','GPK'=>'#F59E0B','TIS'=>'#FBBF24',
        ];

        $labels=[]; $data=[]; $cols=[];
        foreach ($productMap as $label => $field) {
            $total = round($recs->sum($field));
            if ($total > 0) { $labels[]=$label; $data[]=$total; $cols[]=$colors[$label] ?? '#94a3b8'; }
        }
        return compact('labels','data') + ['colors'=>$cols];
    }

    // ════════════════════════════════════════════════════
    // WEIGHT AVG — SUMPRODUCT formula
    // Pen BoCT  = SUMPRODUCT(total × pen_value) / SUM(total)
    // Rumus sama untuk Mahakam dan Dem
    // ════════════════════════════════════════════════════

    private function weightedAvg(Collection $recs): ?float
    {
        $num = $recs->filter(fn($r) => $r->pen_value !== null && $r->total_tonnage > 0)
            ->sum(fn($r) => $r->pen_value * $r->total_tonnage);
        $den = $recs->filter(fn($r) => $r->pen_value !== null && $r->total_tonnage > 0)
            ->sum('total_tonnage');

        return $den > 0 ? round($num / $den, 2) : null;
    }

    private function weightedAvgDem(Collection $recs): ?float
    {
        $num = $recs->filter(fn($r) => $r->dem_value !== null && $r->total_tonnage > 0)
            ->sum(fn($r) => $r->dem_value * $r->total_tonnage);
        $den = $recs->filter(fn($r) => $r->dem_value !== null && $r->total_tonnage > 0)
            ->sum('total_tonnage');

        return $den > 0 ? round($num / $den, 2) : null;
    }

    private function empty(): array
    {
        return ['rows'=>[],'total'=>0,'kpi'=>['Completed'=>0,'In Progress'=>0,'Loading'=>0,'Plan'=>0],
                'chart_company'=>['labels'=>[],'datasets'=>[]],'chart_count'=>['labels'=>[],'datasets'=>[]]];
    }
}