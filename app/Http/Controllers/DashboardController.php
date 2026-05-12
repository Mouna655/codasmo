<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\{Site, DailyProduction};

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $date      = $request->date ? Carbon::parse($request->date) : $this->latestDate();
        $dashboard = $this->build($date);
        return view('dashboard.daily', compact('dashboard','date'));
    }

    public function apiData(Request $request)
    {
        $date = $request->date ? Carbon::parse($request->date) : $this->latestDate();
        return response()->json($this->build($date));
    }

    public function build(Carbon $date): array
    {
        //  dd(DailyProduction::select('report_date')->distinct()->get()); untuk debug

        $rows  = DailyProduction::whereDate('report_date',$date)
                ->where('is_active', true)
                ->with(['site','subSite'])
                ->get();
        $sites = Site::operational()->with('subSites')->get();
        $bySite = $rows->groupBy('site_id');
        // $sites = $sites->filter(fn($site) => $bySite->has($site->id));

        // Load production plans untuk bulan yang sesuai
        $productionPlans = \App\Models\ProductionPlan::where('year', $date->year)
                            ->where('month', $date->month)
                            ->get()
                            ->keyBy('site_id');

        $siteData = [];
        $totals   = ['daily'=>['coal_winning'=>0,'fc_production'=>0],
                     'mtd'  =>['coal_winning'=>0,'rom_stock'=>0,'port_stock_yard'=>0,'fc_production'=>0],
                     'plan' =>0,'achievement_pct'=>0];

        foreach ($sites as $site) {
            $sRows    = $bySite->get($site->id, collect());
            $primary  = $sRows->first(fn($r) => $r->subSite?->is_primary);

            $cwD  = DailyProduction::zeroIfNoise($primary?->coal_winning_daily);
            $cwM  = DailyProduction::zeroIfNoise($primary?->coal_winning_mtd);
            $rom  = DailyProduction::zeroIfNoise($primary?->rom_stock);
            $fcD  = DailyProduction::zeroIfNoise($sRows->sum('fc_production_daily'));
            $fcM  = DailyProduction::zeroIfNoise($sRows->sum('fc_production_mtd'));
            $psyM = DailyProduction::zeroIfNoise($sRows->sum('port_stock_yard_mtd'));

            // Ambil fc_plan dari production_plans berdasarkan site_id dan bulan
            $plan = (float)($productionPlans->get($site->id)?->fc_plan ?? 0);
            $ach  = $plan > 0 ? round($fcM / $plan * 100) : 0;

            $fcBySub = $sRows->map(fn($r) => [
                'label'       => $r->subSite?->code ?? '?',
                'value'       => DailyProduction::zeroIfNoise($r->fc_production_mtd),
                'chart_color' => $r->subSite?->chart_color ?? '#1B2A8A',
            ])->filter(fn($v) => $v['value'] >= 0)->values();

            $siteData[] = [
                'fc_by_sub_site' => $fcBySub,
                'site_id'        => $site->id,
                'code'           => $site->code,
                'daily'          => ['coal_winning'=>$cwD,'fc_production'=>$fcD],
                'mtd'            => ['coal_winning'=>$cwM,'rom_stock'=>$rom,'port_stock_yard'=>$psyM,'fc_production'=>$fcM],
                'plan'           => $plan,
                'achievement_pct'=> $ach,
                'has_data'       => $sRows->count() > 0,
            ];

            $totals['daily']['coal_winning']  += $cwD;
            $totals['daily']['fc_production'] += $fcD;
            $totals['mtd']['coal_winning']    += $cwM;
            $totals['mtd']['rom_stock']       += $rom;
            $totals['mtd']['port_stock_yard'] += $psyM;
            $totals['mtd']['fc_production']   += $fcM;
            $totals['plan']                   += $plan;
        }

        $totals['achievement_pct'] = $totals['plan'] > 0
            ? round($totals['mtd']['fc_production'] / $totals['plan'] * 100) : 0;

        return [
            'date'       => $date->format('Y-m-d'),
            'date_label' => $date->translatedFormat('j F Y'),
            'sites'      => $siteData,
            'totals'     => $totals,
            'last_input' => $rows->max('input_at')
                ? Carbon::parse($rows->max('input_at'))->format('d M Y H:i') : '—',
            'has_data'   => $rows->count() > 0,
        ];
    }

    private function latestDate(): Carbon
    {
        $latest = DailyProduction::max('report_date');
        return $latest ? Carbon::parse($latest) : Carbon::today();
    }

}