<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\{User, Site, SubSite, ProductionPlan, DailyProduction};

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
       
        $this->seedSites();
        $this->seedPlans();
        $this->seedDailyProductions();
    }

    private function seedSites(): void
    {
        // ── Sites ─────────────────────────────────────────────────────
        $sites = [
            ['code'=>'IMM',  'name'=>'PT Indominco Mandiri',                'is_parent'=>false,'sort_order'=>1],
            ['code'=>'TCM',  'name'=>'PT Trubaindo Coal Mining',            'is_parent'=>false,'sort_order'=>2],
            ['code'=>'BEK',  'name'=>'PT Bharinto Ekatama',                 'is_parent'=>false,'sort_order'=>3],
            ['code'=>'GPK',  'name'=>'PT Graha Panca Karsa',                'is_parent'=>false,'sort_order'=>4],
            ['code'=>'JBG',  'name'=>'PT Jorong Barutama Greston',          'is_parent'=>false,'sort_order'=>5],
            ['code'=>'TIS',  'name'=>'PT Tepian Indah Sukses',              'is_parent'=>false,'sort_order'=>6],
            ['code'=>'ITMG', 'name'=>'PT Indo Tambangraya Megah Tbk (ITM)', 'is_parent'=>true, 'sort_order'=>7],
        ];

        // ── Sub-sites (dari kolom "Sub-Site" di Excel) ─────────────────
        // IMM: WB LS, WB HS, EB LS, EB MS, EB HS
        // TCM: LS, HS, MS
        // BEK: LS, HS
        // GPK/JBG/TIS/ITMG: satu sub-site
        $subSites = [
            'IMM'  => [
                ['code'=>'WB LS','name'=>'West Blok Low Sulphur',   'is_primary'=>true, 'chart_color'=>'#1B2A8A','sort_order'=>1],
                ['code'=>'WB HS','name'=>'West Blok High Sulphur',  'is_primary'=>false,'chart_color'=>'#4C6EF5','sort_order'=>2],
                ['code'=>'EB LS','name'=>'East Blok Low Sulphur',   'is_primary'=>false,'chart_color'=>'#74C0FC','sort_order'=>3],
                ['code'=>'EB MS','name'=>'East Blok Medium Sulphur','is_primary'=>false,'chart_color'=>'#5DCAA5','sort_order'=>4],
                ['code'=>'EB HS','name'=>'East Blok High Sulphur',  'is_primary'=>false,'chart_color'=>'#7B6CF5','sort_order'=>5],
            ],
            'TCM'  => [
                ['code'=>'LS','name'=>'Low Sulphur',   'is_primary'=>true, 'chart_color'=>'#1B2A8A','sort_order'=>1],
                ['code'=>'HS','name'=>'High Sulphur',  'is_primary'=>false,'chart_color'=>'#7B6CF5','sort_order'=>2],
                ['code'=>'MS','name'=>'Medium Sulphur','is_primary'=>false,'chart_color'=>'#5DCAA5','sort_order'=>3],
            ],
            'BEK'  => [
                ['code'=>'LS','name'=>'Low Sulphur', 'is_primary'=>true, 'chart_color'=>'#1B2A8A','sort_order'=>1],
                ['code'=>'HS','name'=>'High Sulphur','is_primary'=>false,'chart_color'=>'#5DCAA5','sort_order'=>2],
            ],
            'GPK'  => [['code'=>'GPK', 'name'=>'GPK', 'is_primary'=>true,'chart_color'=>'#74C0FC','sort_order'=>1]],
            'JBG'  => [['code'=>'JBG', 'name'=>'JBG', 'is_primary'=>true,'chart_color'=>'#1B2A8A','sort_order'=>1]],
            'TIS'  => [['code'=>'TIS', 'name'=>'TIS', 'is_primary'=>true,'chart_color'=>'#00C9C9','sort_order'=>1]],
            'ITMG' => [['code'=>'ITMG','name'=>'ITMG','is_primary'=>true,'chart_color'=>'#1D9E75','sort_order'=>1]],
        ];

        foreach ($sites as $s) {
            $site = Site::updateOrCreate(['code'=>$s['code']], $s);
            foreach ($subSites[$s['code']] ?? [] as $sub) {
                SubSite::updateOrCreate(
                    ['site_id'=>$site->id,'code'=>$sub['code']],
                    array_merge($sub,['site_id'=>$site->id])
                );
            }
        }
    }

    private function seedPlans(): void
    {
        // Kolom M (FC Plan) dari Excel:
        // IMM=480000, TCM=110000, BEK=450000, GPK=510000, JBG=0, TIS=200000
        $plans = [
            'IMM'=>389000, 'TCM'=>123000, 'BEK'=>438000,
            'GPK'=>632000, 'JBG'=>0,      'TIS'=>345000,
        ];
        $year = now()->year;
        foreach ($plans as $code => $plan) {
            $site = Site::where('code',$code)->first();
            if (!$site) continue;
            for ($m = 1; $m <= 12; $m++) {
                ProductionPlan::updateOrCreate(
                    ['site_id'=>$site->id,'year'=>$year,'month'=>$m],
                    ['fc_plan'=>$plan,'coal_winning_plan'=>round($plan*1.05)]
                );
            }
        }
    }

    private function seedDailyProductions(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DailyProduction::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ── Data mentah dari Excel baris per baris ─────────────────────
        // Format: site → sub_site → [fc_daily, fc_mtd, psy_daily, psy_mtd, cw_daily, cw_mtd, rom_stock]
        // Nilai 0.0001 di Excel = nol (placeholder), kita simpan 0
        // cw & rom hanya untuk primary sub-site, lainnya null
        $excelSnapshot = [
            'IMM' => [
                'WB LS' => ['fc_d'=>0,     'fc_m'=>1231,   'psy_d'=>23904,'psy_m'=>21893, 'cw_d'=>13948,'cw_m'=>210853,'rom'=>0],
                'WB HS' => ['fc_d'=>0,     'fc_m'=>1984,   'psy_d'=>17943,'psy_m'=>13927, 'cw_d'=>null, 'cw_m'=>null,  'rom'=>null],
                'EB LS' => ['fc_d'=>0,     'fc_m'=>0,      'psy_d'=>0,    'psy_m'=>0,     'cw_d'=>null, 'cw_m'=>null,  'rom'=>null],
                'EB MS' => ['fc_d'=>7658,  'fc_m'=>37891,  'psy_d'=>16892,'psy_m'=>23498, 'cw_d'=>null, 'cw_m'=>null,  'rom'=>null],
                'EB HS' => ['fc_d'=>16921, 'fc_m'=>112994, 'psy_d'=>43985,'psy_m'=>47892, 'cw_d'=>null, 'cw_m'=>null,  'rom'=>null],
            ],
            'TCM' => [
                'LS' => ['fc_d'=>1293, 'fc_m'=>62892, 'psy_d'=>76892,'psy_m'=>84921, 'cw_d'=>7294, 'cw_m'=>126498,'rom'=>123456],
                'HS' => ['fc_d'=>6381, 'fc_m'=>35901, 'psy_d'=>43891,'psy_m'=>42908, 'cw_d'=>null, 'cw_m'=>null,  'rom'=>null],
                'MS' => ['fc_d'=>0,    'fc_m'=>0,     'psy_d'=>27592,'psy_m'=>24882, 'cw_d'=>null, 'cw_m'=>null,  'rom'=>null],
            ],
            'BEK' => [
                'LS' => ['fc_d'=>23489,'fc_m'=>173908,'psy_d'=>0,    'psy_m'=>0,     'cw_d'=>13894,'cw_m'=>198034,'rom'=>218942],
                'HS' => ['fc_d'=>0,    'fc_m'=>8932, 'psy_d'=>0,    'psy_m'=>0,     'cw_d'=>5982, 'cw_m'=>23985, 'rom'=>null],
            ],
            'GPK' => [
                'GPK' => ['fc_d'=>4012,'fc_m'=>82719,'psy_d'=>6902,'psy_m'=>6458,'cw_d'=>6234,'cw_m'=>82385,'rom'=>21942],
            ],
            'JBG' => [
                'JBG' => ['fc_d'=>0,'fc_m'=>0,'psy_d'=>2891,'psy_m'=>3458,'cw_d'=>0,'cw_m'=>0,'rom'=>0],
            ],
            'TIS' => [
                'TIS' => ['fc_d'=>0,'fc_m'=>4891,'psy_d'=>0,'psy_m'=>0,'cw_d'=>3499,'cw_m'=>11029,'rom'=>82194],
            ],
        ];

        $operator    = User::where('role','operator')->first();
        $snapshotDate = '2026-03-17'; // tanggal dari Excel
        $today        = Carbon::today();

        // ── Seed tanggal Excel persis (snapshot) ──────────────────────
        foreach ($excelSnapshot as $siteCode => $subSites) {
            $site = Site::where('code',$siteCode)->first();
            if (!$site) continue;
            foreach ($subSites as $subCode => $d) {
                $sub = SubSite::where('site_id',$site->id)->where('code',$subCode)->first();
                if (!$sub) continue;
                DailyProduction::updateOrCreate(
                    ['report_date'=>$snapshotDate,'site_id'=>$site->id,'sub_site_id'=>$sub->id],
                    [
                        'fc_production_daily'   => $d['fc_d'],
                        'fc_production_mtd'     => $d['fc_m'],
                        'port_stock_yard_daily' => $d['psy_d'],
                        'port_stock_yard_mtd'   => $d['psy_m'],
                        'coal_winning_daily'    => $d['cw_d'],
                        'coal_winning_mtd'      => $d['cw_m'],
                        'rom_stock'             => $d['rom'],
                        'created_by'            => $operator?->id,
                        'input_at'              => Carbon::parse($snapshotDate)->setTime(10,50),
                    ]
                );
            }
        }

        // ── Seed 29 hari tambahan dengan variasi realistis ─────────────
        $baseDaily = [
            'IMM'=>['WB LS'=>[0,1400,27000,22000,17535,null,null,null],
                    'WB HS'=>[0,1400,16000,16000,null,null,null,null],
                    'EB LS'=>[0,0,0,0,null,null,null,null],
                    'EB MS'=>[6000,28000,13000,19000,null,null,null,null],
                    'EB HS'=>[17000,123000,46000,48000,null,null,null,null]],
            'TCM'=>['LS'=>[6000,46000,49000,65000,7000,1320000,123000,null],
                    'HS'=>[5000,27000,33000,46000,null,null,null,null],
                    'MS'=>[0,0,23000,25000,null,null,null,null]],
            'BEK'=>['LS'=>[18000,210000,0,0,16000,130000,340000,null],
                    'HS'=>[0,65000,0,0,3500,22000,null,null]],
            'GPK'=>['GPK'=>[7000,81000,6000,8500,5000,64000,31000,null]],
            'JBG'=>['JBG'=>[0,0,1100,2300,0,0,0,null]],
            'TIS'=>['TIS'=>[0,4500,0,0,4200,12400,79000,null]],
        ];

        for ($d = 1; $d <= 29; $d++) {
            $date = $today->copy()->subDays($d);
            if ($date->format('Y-m-d') === $snapshotDate) continue;
            $day = $date->day;

            foreach ($baseDaily as $siteCode => $subSiteMap) {
                $site = Site::where('code',$siteCode)->first();
                if (!$site) continue;
                foreach ($subSiteMap as $subCode => $vals) {
                    $sub = SubSite::where('site_id',$site->id)->where('code',$subCode)->first();
                    if (!$sub) continue;
                    $v = fn($base) => $base > 0 ? max(0,round($base*(1+(rand(-12,12)/100)))) : 0;
                    DailyProduction::updateOrCreate(
                        ['report_date'=>$date->toDateString(),'site_id'=>$site->id,'sub_site_id'=>$sub->id],
                        [
                            'fc_production_daily'   => $v($vals[0]),
                            'fc_production_mtd'     => $v($vals[1]*min($day,17)/17),
                            'port_stock_yard_daily' => $v($vals[2]),
                            'port_stock_yard_mtd'   => $v($vals[3]*min($day,17)/17),
                            'coal_winning_daily'    => $vals[4] !== null ? $v($vals[4]) : null,
                            'coal_winning_mtd'      => $vals[5] !== null ? $v($vals[5]*min($day,17)/17) : null,
                            'rom_stock'             => $vals[6] !== null ? $v($vals[6]) : null,
                            'created_by'            => $operator?->id,
                            'input_at'              => $date->copy()->setTime(10,30),
                        ]
                    );
                }
            }
        }
    }
}