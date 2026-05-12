<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EBP 3rd Party Coal — ITM Dashboard</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('img/ITM_Logo_3.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ── Animations ──────────────────────────── */
        @keyframes fadeUp   { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
        @keyframes countUp  { from{opacity:0;transform:scale(.85)} to{opacity:1;transform:scale(1)} }
        @keyframes barGrow  { from{width:0} to{width:var(--w)} }
        @keyframes pulse    { 0%,100%{opacity:1} 50%{opacity:.4} }

        body { font-family:'Inter',sans-serif; background:#EFF4FB; }

        /* ── Topbar ──────────────────────────────── */
        .topbar {
            background:white; border-bottom:1px solid #e2e8f0;
            height:56px; padding:0 20px;
            display:flex; align-items:center; justify-content:space-between;
            position:sticky; top:0; z-index:50;
        }

        /* ── Page wrapper ────────────────────────── */
        .page { padding:16px 20px; max-width:1600px; margin:0 auto; }

        /* ── Section title card ──────────────────── */
        .page-header {
            background:linear-gradient(135deg,#1B2A8A 0%,#2851A3 60%,#1D9E75 100%);
            border-radius:18px; padding:18px 24px;
            display:flex; align-items:center; justify-content:space-between;
            margin-bottom:16px; position:relative; overflow:hidden;
        }
        .page-header::before {
            content:''; position:absolute; inset:0; opacity:.08;
            background-image:linear-gradient(rgba(255,255,255,.5) 1px,transparent 1px),
                             linear-gradient(90deg,rgba(255,255,255,.5) 1px,transparent 1px);
            background-size:28px 28px;
        }

        /* ── Cards ───────────────────────────────── */
        .card {
            background:white; border-radius:18px;
            box-shadow:0 2px 14px rgba(27,42,138,.08);
            overflow:hidden;
        }
        .card-hd {
            padding:12px 18px; border-bottom:1px solid #f1f5f9;
            display:flex; align-items:center; justify-content:space-between;
        }
        .card-hd h3 { font-size:12px; font-weight:800; color:#1B2A8A; text-transform:uppercase; letter-spacing:.06em; margin:0; }
        .card-body  { padding:16px 18px; }

        /* ── KPI card ────────────────────────────── */
        .kpi-card {
            background:white; border-radius:18px;
            box-shadow:0 2px 14px rgba(27,42,138,.08);
            padding:20px 24px; display:flex; flex-direction:column; gap:4px;
        }
        .kpi-label { font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.07em; }
        .kpi-value { font-size:40px; font-weight:900; line-height:1; }
        .kpi-sub   { font-size:11px; color:#64748b; margin-top:4px; }

        /* ── Grid layouts ────────────────────────── */
        .grid-main {
            display:grid;
            grid-template-columns:280px 1fr 180px;
            gap:14px;
            margin-bottom:14px;
        }
        @media(max-width:1200px) {
            .grid-main { grid-template-columns:1fr; }
        }

        /* ── Horizontal bar chart (shipper) ──────── */
        .shipper-list { display:flex; flex-direction:column; gap:6px; }
        .shipper-row  { display:flex; align-items:center; gap:8px; }
        .shipper-name { font-size:10px; font-weight:700; color:#334155; width:44px; flex-shrink:0; text-align:right; }
        .shipper-track {
            flex:1; height:16px; background:#f1f5f9; border-radius:4px;
            overflow:hidden; position:relative; cursor:pointer;
        }
        .shipper-fill {
            height:100%; border-radius:4px; width:0;
            transition:width 1.2s cubic-bezier(.34,1.56,.64,1);
            position:relative;
        }
        .shipper-fill::after {
            content:attr(data-val);
            position:absolute; right:4px; top:50%;
            transform:translateY(-50%);
            font-size:9px; font-weight:800;
            color:white; white-space:nowrap;
        }
        .shipper-pct { font-size:10px; font-weight:800; color:#334155; width:42px; flex-shrink:0; }

        /* ── Donut tooltip ───────────────────────── */
        .donut-tooltip {
            position:absolute; background:#0F172A; color:white;
            border-radius:12px; padding:10px 14px; font-size:11px;
            pointer-events:none; opacity:0; transition:opacity .2s;
            z-index:10; min-width:180px; box-shadow:0 8px 24px rgba(0,0,0,.3);
        }
        .donut-tooltip.show { opacity:1; }

        /* ── Achieve badge ───────────────────────── */
        .ach-badge {
            display:inline-flex; align-items:center;
            padding:2px 7px; border-radius:999px;
            font-size:9px; font-weight:800;
        }
        .ach-over  { background:#dcfce7; color:#166534; }
        .ach-good  { background:#dbeafe; color:#1e40af; }
        .ach-mid   { background:#fef9c3; color:#92400e; }
        .ach-low   { background:#fee2e2; color:#991b1b; }
        .ach-zero  { background:#f1f5f9; color:#64748b; }
    </style>
</head>
<body>

{{-- TOPBAR --}}
<header class="topbar">
    <div style="display:flex;align-items:center;gap:16px">
        <a href="{{ route('home') }}"
           style="display:flex;align-items:center;gap:5px;font-size:11px;font-weight:600;
                  color:#64748b;text-decoration:none">
            <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Back to Homepage
        </a>
        <div style="width:1px;height:20px;background:#e2e8f0"></div>
        <div style="display:flex;align-items:center;gap:8px">
            <!-- <div style="width:32px;height:32px;background:linear-gradient(135deg,#6B21A8,#7C3AED);
                        border-radius:9px;display:flex;align-items:center;justify-content:center">
                <svg style="width:16px;height:16px;stroke:white;fill:none;stroke-width:1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/>
                </svg>
            </div> -->
            <div>
                <p style="font-size:12px;font-weight:900;color:#1B2A8A;margin:0;line-height:1">EBP 3rd Party Coal</p>
                <p style="font-size:9px;color:#94a3b8;margin:0">YTD Summary Dashboard</p>
            </div>
        </div>
    </div>

    <div style="display:flex;align-items:center;gap:10px">
        {{-- Live --}}
        <div style="display:flex;align-items:center;gap:5px;font-size:11px;color:#059669;font-weight:600">
            <span style="width:7px;height:7px;background:#10b981;border-radius:50%;
                         animation:pulse 2s infinite;display:inline-block"></span>
            Live
        </div>
        <div id="clock" style="font-size:11px;color:#64748b;background:#f8fafc;
                                border:1px solid #e2e8f0;padding:4px 10px;border-radius:8px;font-weight:500"></div>

        {{-- Year selector --}}
        <select onchange="window.location.href='/third-party?year='+this.value"
                style="padding:5px 10px;font-size:11px;font-weight:600;background:white;
                       border:1px solid #e2e8f0;border-radius:9px;cursor:pointer;
                       color:#334155;outline:none;font-family:Inter,sans-serif">
            @foreach([now()->year - 1, now()->year, now()->year + 1] as $y)
            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>

        {{-- Export --}}
        <button onclick="exportReport('png')"
                style="display:flex;align-items:center;gap:5px;padding:6px 12px;font-size:11px;
                       font-weight:700;border:1px solid #e2e8f0;background:white;color:#475569;
                       border-radius:9px;cursor:pointer;font-family:Inter,sans-serif"
                onmouseover="this.style.borderColor='#6B21A8';this.style.color='#6B21A8'"
                onmouseout="this.style.borderColor='#e2e8f0';this.style.color='#475569'">
            <svg style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            PNG
        </button>
        <button onclick="exportReport('jpg')"
                style="display:flex;align-items:center;gap:5px;padding:6px 12px;font-size:11px;
                       font-weight:700;border:none;background:linear-gradient(135deg,#6B21A8,#7C3AED);
                       color:white;border-radius:9px;cursor:pointer;font-family:Inter,sans-serif"
                onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
            <svg style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            JPG
        </button>

    </div>
</header>

{{-- MAIN CONTENT --}}
<div class="page" id="report-canvas">

    {{-- Page header --}}
    <div class="page-header">
        <div style="position:relative;z-index:1">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px">
                <span style="width:7px;height:7px;background:#34d399;border-radius:50%;
                             display:inline-block;animation:pulse 2s infinite"></span>
                <span style="font-size:9px;font-weight:600;color:rgba(255,255,255,.7);
                             text-transform:uppercase;letter-spacing:.07em">
                    Last Update on {{ $data['last_update'] }}
                </span>
            </div>
            <h1 style="font-size:22px;font-weight:900;color:white;margin:0;line-height:1.1">
                <span style="display:flex; align-items:center;gap:6px">
                    <img src="{{ asset('img/ITM_Logo_3.png') }}" alt="ITM Logo" style="width:64px;height:64px;object-fit:contain;border-radius:12px;margin-bottom:16px;padding:6px;">
                 EBP SUMMARY 3rd PARTY COAL YTD
                </span>
            </h1>
            <p style="font-size:11px;color:rgba(255,255,255,.6);margin:4px 0 0">
                Year {{ $year }} · Data  {{ $data['last_update'] }}
            </p>
        </div>
        <!-- <div style="position:relative;z-index:1;text-align:right">
            <p style="font-size:10px;color:rgba(255,255,255,.5);margin:0 0 4px;text-transform:uppercase;letter-spacing:.06em">Navigation</p>
            <div style="display:flex;gap:6px;justify-content:flex-end">
                @foreach(['Supply Chain','Demand','Supply','Logistic','Financial'] as $tab)
                <span style="padding:4px 10px;border-radius:7px;font-size:10px;font-weight:700;
                             cursor:pointer;transition:all .15s;
                             {{ $tab === 'Supply' ? 'background:#1B2A8A;color:white' : 'background:rgba(255,255,255,.15);color:rgba(255,255,255,.8)' }}">
                    {{ $tab }}
                </span>
                @endforeach
            </div>
        </div> -->
    </div>

    {{-- ── MAIN GRID: Shipper | Donut | KPI ────────────────── --}}
    <div class="grid-main">

        {{-- ① Achieve to Plans by Shipper --}}
        <div class="card" style="animation:fadeUp .5s ease forwards">
            <div class="card-hd">
                <h3>Achieve to Plans by Shipper</h3>
            </div>
            <div class="card-body" style="padding:12px 16px">
                <div class="shipper-list" id="shipper-list">
                    @foreach($data['by_shipper'] as $s)
                    @php
                        $ach   = $s['achieve'];
                        $cap   = min($ach, 200); // cap visual di 200%
                        $wpct  = round($cap / 200 * 100); // 200% = full bar
                        $color = $ach >= 100 ? '#1B2A8A' : ($ach >= 80 ? '#2563EB' : ($ach >= 50 ? '#F59E0B' : '#EF4444'));
                        $badge = $ach >= 100 ? 'ach-over' : ($ach >= 80 ? 'ach-good' : ($ach >= 50 ? 'ach-mid' : 'ach-low'));
                    @endphp
                    <div class="shipper-row">
                        <span class="shipper-name">{{ $s['shipper'] }}</span>
                        <div class="shipper-track" title="{{ $s['shipper'] }}: Plan={{ number_format($s['plan']) }}, Actual={{ number_format($s['actual']) }}">
                            <div class="shipper-fill"
                                 style="background:{{ $color }};--w:{{ $wpct }}%"
                                 data-val="{{ $ach }}%">
                            </div>
                            {{-- 100% marker --}}
                            <div style="position:absolute;left:50%;top:0;height:100%;
                                        width:1px;background:rgba(0,0,0,.15)"></div>
                        </div>
                        <span class="shipper-pct {{ $badge }}">{{ $ach }}%</span>
                    </div>
                    @endforeach

                    @if(empty($data['by_shipper']))
                    <p style="font-size:11px;color:#94a3b8;text-align:center;padding:20px 0">
                        there is no data yet
                    </p>
                    @endif
                </div>
                {{-- X-axis labels --}}
                <div style="display:flex;justify-content:space-between;margin-top:8px;padding-left:52px">
                    <span style="font-size:8px;color:#94a3b8">0%</span>
                    <span style="font-size:8px;color:#94a3b8">100%</span>
                    <span style="font-size:8px;color:#94a3b8">200%</span>
                </div>
            </div>
        </div>

        {{-- ② Actual Supplier by Qualities (Donut Interaktif) --}}
        <div class="card" style="animation:fadeUp .5s .1s ease both">
            <div class="card-hd">
                <h3>Actual Supplier by Qualities</h3>
                <span style="font-size:9px;color:#94a3b8;font-weight:500">Klik slice untuk detail</span>
            </div>
            <div class="card-body" style="display:flex;gap:20px;align-items:flex-start">

                {{-- Donut --}}
                <div style="position:relative;flex-shrink:0">
                    <canvas id="qualityDonut" width="260" height="260"></canvas>
                    {{-- Center text --}}
                    <div id="donut-center"
                         style="position:absolute;inset:0;display:flex;flex-direction:column;
                                align-items:center;justify-content:center;pointer-events:none;
                                transition:all .25s">
                        <span style="font-size:11px;color:#94a3b8;font-weight:500">Total Actual</span>
                        <span style="font-size:22px;font-weight:900;color:#1B2A8A;line-height:1.1"
                              id="donut-center-val">{{ $data['kpi']['sum_actual_k'] }}</span>
                        <span style="font-size:9px;color:#94a3b8">ton</span>
                    </div>
                    {{-- Tooltip --}}
                    <div class="donut-tooltip" id="donut-tooltip"></div>
                </div>

                {{-- Legend + detail panel --}}
                <div style="flex:1;min-width:0">
                    {{-- Quality legend --}}
                    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:16px">
                        @php
                            $qColors = [
                                'ICI 1' => '#60A5FA',
                                'ICI 2' => '#1B2A8A',
                                'ICI 3' => '#F472B6',
                                'ICI 4' => '#A78BFA',
                                'ICI 5' => '#F97316',
                            ];
                        @endphp
                        @foreach($data['by_quality'] as $q)
                        @php $color = $qColors[$q['quality']] ?? '#94a3b8'; @endphp
                        <div class="quality-leg" data-quality="{{ $q['quality'] }}"
                             style="display:flex;align-items:center;gap:8px;cursor:pointer;
                                    padding:6px 8px;border-radius:8px;transition:background .15s"
                             onmouseover="this.style.background='#f8fafc'"
                             onmouseout="this.style.background='transparent'"
                             onclick="highlightQuality('{{ $q['quality'] }}')">
                            <div style="width:10px;height:10px;border-radius:50%;
                                        background:{{ $color }};flex-shrink:0"></div>
                            <div style="flex:1;min-width:0">
                                <div style="display:flex;align-items:baseline;justify-content:space-between">
                                    <span style="font-size:11px;font-weight:700;color:#334155">{{ $q['quality'] }}</span>
                                    <span style="font-size:11px;font-weight:900;color:{{ $color }}">
                                        {{ number_format($q['actual'] / 1000, 1) }}K
                                    </span>
                                </div>
                                <div style="height:4px;background:#f1f5f9;border-radius:2px;margin-top:3px;overflow:hidden">
                                    <div style="height:100%;background:{{ $color }};border-radius:2px;
                                                width:{{ $data['kpi']['sum_actual'] > 0 ? round($q['actual']/$data['kpi']['sum_actual']*100) : 0 }}%;
                                                transition:width .8s ease"></div>
                                </div>
                            </div>
                            <span style="font-size:10px;color:#94a3b8;flex-shrink:0;width:36px;text-align:right">
                                {{ $data['kpi']['sum_actual'] > 0 ? round($q['actual']/$data['kpi']['sum_actual']*100,1) : 0 }}%
                            </span>
                        </div>
                        @endforeach
                    </div>

                    {{-- Detail panel (muncul saat klik) --}}
                    <div id="quality-detail" style="display:none;background:#f8fafc;
                         border-radius:12px;padding:12px;border:1px solid #e2e8f0">
                        <p id="quality-detail-title" style="font-size:10px;font-weight:800;
                           color:#1B2A8A;text-transform:uppercase;letter-spacing:.06em;margin:0 0 8px"></p>
                        <div id="quality-detail-body" style="display:flex;flex-direction:column;gap:5px"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ③ KPI Cards --}}
        <div style="display:flex;flex-direction:column;gap:12px;animation:fadeUp .5s .2s ease both">
            {{-- Achieve to Plans --}}
            <div class="kpi-card">
                <span class="kpi-label">Achieve to Plans</span>
                <span class="kpi-value" id="kpi-achieve"
                      style="color:{{ $data['kpi']['ytd_achieve'] >= 100 ? '#1D9E75' : ($data['kpi']['ytd_achieve'] >= 80 ? '#1B2A8A' : '#F59E0B') }}">
                    {{ $data['kpi']['ytd_achieve'] }}%
                </span>
                <span class="kpi-sub">YTD {{ $year }}</span>

                {{-- Mini gauge --}}
                <div style="margin-top:10px;height:6px;background:#f1f5f9;border-radius:999px;overflow:hidden">
                    <div id="kpi-gauge"
                         style="height:100%;border-radius:999px;width:0;transition:width 1.2s ease;
                                background:{{ $data['kpi']['ytd_achieve'] >= 100 ? '#1D9E75' : ($data['kpi']['ytd_achieve'] >= 80 ? '#1B2A8A' : '#F59E0B') }}"
                         data-target="{{ min(100, $data['kpi']['ytd_achieve']) }}">
                    </div>
                </div>
                <div style="display:flex;justify-content:space-between;margin-top:3px">
                    <span style="font-size:8px;color:#94a3b8">0%</span>
                    <span style="font-size:8px;color:#94a3b8">100%</span>
                </div>
            </div>

            {{-- Sum of Actual --}}
            <div class="kpi-card">
                <span class="kpi-label">Total Actual</span>
                <span class="kpi-value" id="kpi-actual" style="color:#1B2A8A">
                    {{ $data['kpi']['sum_actual_k'] }}
                </span>
                <!-- <span class="kpi-sub">ton realisasi YTD</span> -->
            </div>

            {{-- Plan vs Actual mini --}}
            <div class="kpi-card" style="flex:1">
                <span class="kpi-label">Plan vs Actual</span>
                <div style="margin-top:8px;display:flex;flex-direction:column;gap:8px">
                    <div>
                        <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                            <span style="font-size:10px;color:#64748b">Plan</span>
                            <span style="font-size:10px;font-weight:700;color:#334155">
                                {{ number_format($data['kpi']['total_plan'] / 1000, 1) }}K
                            </span>
                        </div>
                        <div style="height:5px;background:#f1f5f9;border-radius:999px;overflow:hidden">
                            <div style="height:100%;width:100%;background:#CBD5E1;border-radius:999px"></div>
                        </div>
                    </div>
                    <div>
                        <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                            <span style="font-size:10px;color:#64748b">Actual</span>
                            <span style="font-size:10px;font-weight:700;color:#1B2A8A">
                                {{ $data['kpi']['sum_actual_k'] }}
                            </span>
                        </div>
                        <div style="height:5px;background:#f1f5f9;border-radius:999px;overflow:hidden">
                            <div id="actual-bar"
                                 style="height:100%;width:0;background:#1B2A8A;border-radius:999px;
                                        transition:width 1.2s ease"
                                 data-target="{{ $data['kpi']['total_plan'] > 0 ? min(100, round($data['kpi']['sum_actual']/$data['kpi']['total_plan']*100)) : 0 }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── BOTTOM: Bar + Line Chart ────────────────────────── --}}
    <div class="card" style="animation:fadeUp .5s .3s ease both">
        <div class="card-hd">
            <h3>Plan vs Actual vs Achieve to Plans</h3>
            <div style="display:flex;align-items:center;gap:12px">
                <div style="display:flex;align-items:center;gap:4px">
                    <div style="width:12px;height:12px;background:#CBD5E1;border-radius:2px"></div>
                    <span style="font-size:10px;color:#64748b">Plan</span>
                </div>
                <div style="display:flex;align-items:center;gap:4px">
                    <div style="width:12px;height:12px;background:#1B2A8A;border-radius:2px"></div>
                    <span style="font-size:10px;color:#64748b">Actual</span>
                </div>
                <div style="display:flex;align-items:center;gap:4px">
                    <div style="width:20px;height:2px;background:#F59E0B;border-radius:1px"></div>
                    <span style="font-size:10px;color:#64748b">Achieve %</span>
                </div>
                <span style="font-size:9px;color:#94a3b8">* Compared to Outlook Plan</span>
            </div>
        </div>
        <div class="card-body">
            <div style="height:260px;position:relative">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div style="display:flex;align-items:center;justify-content:space-between;
                padding:8px 2px;margin-top:8px">
        <span style="font-size:9px;color:#94a3b8">
            <strong style="color:#64748b">ITM — EBP 3rd Party Coal</strong>
            · YTD {{ $year }} · Confidential
        </span>
        <span style="font-size:9px;color:#94a3b8">
            Generated {{ now()->format('d M Y H:i:s') }}
        </span>
    </div>
</div>



<script>
const DATA      = @json($data);
const YEAR      = {{ $year }};

/* ── Color map per quality ──────────────────────────────── */
const Q_COLORS = {
    'ICI 1': '#60A5FA',
    'ICI 2': '#1B2A8A',
    'ICI 3': '#F472B6',
    'ICI 4': '#A78BFA',
    'ICI 5': '#F97316',
};

/* ── Clock ──────────────────────────────────────────────── */
(function tick(){
    const el = document.getElementById('clock');
    if(el) el.textContent = new Date().toLocaleString('id-ID',{
        day:'2-digit',month:'short',year:'numeric',
        hour:'2-digit',minute:'2-digit',second:'2-digit'
    });
    setTimeout(tick,1000);
})();

/* ── Animate shipper bars on load ───────────────────────── */
window.addEventListener('load', () => {
    // Shipper bars
    document.querySelectorAll('.shipper-fill').forEach((el, i) => {
        const target = el.style.getPropertyValue('--w');
        el.style.width = '0';
        setTimeout(() => { el.style.width = target; }, 100 + i * 40);
    });

    // KPI gauge
    const gauge = document.getElementById('kpi-gauge');
    if(gauge) {
        const t = parseFloat(gauge.dataset.target);
        setTimeout(() => { gauge.style.width = t + '%'; }, 200);
    }

    // Actual bar
    const abar = document.getElementById('actual-bar');
    if(abar) {
        const t = parseFloat(abar.dataset.target);
        setTimeout(() => { abar.style.width = t + '%'; }, 300);
    }
});

/* ── Donut Chart ────────────────────────────────────────── */
const qualityData = DATA.by_quality;
let donutChart;

window.addEventListener('DOMContentLoaded', () => {
    const donutCtx = document.getElementById('qualityDonut').getContext('2d');

    donutChart = new Chart(donutCtx, {
        type: 'doughnut',
        data: {
            labels:   qualityData.map(q => q.quality),
            datasets: [{
                data:            qualityData.map(q => q.actual),
                backgroundColor: qualityData.map(q => Q_COLORS[q.quality] || '#94a3b8'),
                borderWidth:     3,
                borderColor:     '#ffffff',
                hoverBorderWidth:4,
                hoverOffset:     10,
            }]
        },
        options: {
            cutout: '62%',
            responsive: false,
            animation: { duration: 1000, easing: 'easeInOutQuart' },
            plugins: {
                legend:  { display: false },
                tooltip: { enabled: false }, // custom tooltip
            },
            onClick: (event, elements) => {
                if(elements.length === 0) return;
                const idx     = elements[0].index;
                const quality = qualityData[idx];
                showQualityDetail(quality);
            },
            onHover: (event, elements) => {
                const canvas = event.native.target;
                canvas.style.cursor = elements.length > 0 ? 'pointer' : 'default';

                if(elements.length > 0) {
                    const idx     = elements[0].index;
                    const quality = qualityData[idx];
                    const total   = qualityData.reduce((s, q) => s + q.actual, 0);
                    const pct     = total > 0 ? (quality.actual / total * 100).toFixed(1) : 0;

                    // Update center
                    document.getElementById('donut-center-val').textContent =
                        (quality.actual / 1000).toFixed(1) + 'K';
                    document.querySelector('#donut-center span:first-child').textContent = quality.quality;
                } else {
                    document.getElementById('donut-center-val').textContent =
                        DATA.kpi.sum_actual_k;
                    document.querySelector('#donut-center span:first-child').textContent = 'Total Actual';
                }
            }
        }
    });

    /* ── Monthly Bar + Line Chart ───────────────────────────── */
    const months  = DATA.by_month.map(m => m.month_name);
    const plans   = DATA.by_month.map(m => m.plan);
    const actuals = DATA.by_month.map(m => m.actual);
    const achieves= DATA.by_month.map(m => m.achieve);

    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');

    new Chart(monthlyCtx, {
        data: {
            labels:   months,
            datasets: [
                {
                    type:            'bar',
                    label:           'Plan',
                    data:            plans,
                    backgroundColor: 'rgba(203,213,225,0.8)',
                    borderRadius:    6,
                    borderSkipped:   false,
                    order:           2,
                    yAxisID:         'y',
                },
                {
                    type:            'bar',
                    label:           'Actual',
                    data:            actuals,
                    backgroundColor: 'rgba(27,42,138,0.85)',
                    borderRadius:    6,
                    borderSkipped:   false,
                    order:           1,
                    yAxisID:         'y',
                },
                {
                    type:         'line',
                    label:        'Achieve to Plans (%)',
                    data:         achieves,
                    borderColor:  '#F59E0B',
                    backgroundColor: 'rgba(245,158,11,.15)',
                    borderWidth:  2.5,
                    pointRadius:  5,
                    pointHoverRadius: 8,
                    pointBackgroundColor: '#F59E0B',
                    pointBorderColor:     '#ffffff',
                    pointBorderWidth:     2,
                    tension:      0.4,
                    fill:         false,
                    order:        0,
                    yAxisID:      'y2',
                },
            ]
        },
        options: {
            responsive:          true,
            maintainAspectRatio: false,
            interaction:         { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0F172A',
                    padding:         14,
                    cornerRadius:    10,
                    titleFont:       { family: 'Inter', size: 12, weight: '700' },
                    bodyFont:        { family: 'Inter', size: 11 },
                    callbacks: {
                        label: ctx => {
                            if(ctx.dataset.yAxisID === 'y2') {
                                return ` Achieve: ${ctx.parsed.y}%`;
                            }
                            return ` ${ctx.dataset.label}: ${(ctx.parsed.y/1000).toFixed(1)}K ton`;
                        }
                    }
                },
                // Data labels on bars
                datalabels: false,
            },
            scales: {
                x: {
                    grid:  { color: 'rgba(148,163,184,.12)' },
                    ticks: { font: { family: 'Inter', size: 11 }, color: '#64748B' },
                },
                y: {
                    position: 'left',
                    grid:     { color: 'rgba(148,163,184,.12)' },
                    ticks: {
                        font:     { family: 'Inter', size: 10 },
                        color:    '#94A3B8',
                        callback: v => (v >= 1000 ? (v/1000).toFixed(0)+'K' : v),
                    },
                    title: { display:true, text:'Tonnage', font:{size:9}, color:'#94A3B8' },
                },
                y2: {
                    position: 'right',
                    grid:     { drawOnChartArea: false },
                    min:      0,
                    max:      200,
                    ticks: {
                        font:     { family: 'Inter', size: 10 },
                        color:    '#F59E0B',
                        callback: v => v + '%',
                        stepSize: 50,
                    },
                    title: { display:true, text:'Achieve %', font:{size:9}, color:'#F59E0B' },
                },
            },
            animation: { duration: 1200, easing: 'easeInOutQuart' },
        },
        plugins: [{
            // Label di atas bar actual yang punya data
            afterDatasetsDraw(chart) {
                const { ctx } = chart;
                const dataset = chart.data.datasets[2]; // achieve line
                chart.getDatasetMeta(2).data.forEach((point, i) => {
                    const v = achieves[i];
                    if(v <= 0) return;
                    ctx.save();
                    ctx.font = '700 10px Inter';
                    ctx.fillStyle = '#F59E0B';
                    ctx.textAlign  = 'center';
                    ctx.fillText(v + '%', point.x, point.y - 10);
                    ctx.restore();
                });
            }
        }]
    });
});

/* ── Show quality detail (klik donut atau legend) ─────── */
function showQualityDetail(quality) {
    const panel = document.getElementById('quality-detail');
    const title = document.getElementById('quality-detail-title');
    const body  = document.getElementById('quality-detail-body');
    const color = Q_COLORS[quality.quality] || '#94a3b8';

    title.textContent = quality.quality + ' — Detail Shipper';
    title.style.color  = color;

    body.innerHTML = (quality.shippers || []).map(s => {
        const ach = s.plan > 0 ? (s.actual / s.plan * 100).toFixed(1) : 0;
        return `
        <div style="display:flex;align-items:center;justify-content:space-between;
                    font-size:11px;padding:3px 0;border-bottom:1px solid #f1f5f9">
            <span style="font-weight:700;color:#334155;width:48px">${s.shipper}</span>
            <div style="flex:1;margin:0 8px;height:4px;background:#f1f5f9;border-radius:2px;overflow:hidden">
                <div style="height:100%;background:${color};width:${Math.min(100, ach)}%;border-radius:2px"></div>
            </div>
            <span style="color:${color};font-weight:800;width:44px;text-align:right">
                ${(s.actual/1000).toFixed(1)}K
            </span>
        </div>`;
    }).join('');

    panel.style.display = 'block';
    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function highlightQuality(qualityName) {
    const quality = qualityData.find(q => q.quality === qualityName);
    if(quality) showQualityDetail(quality);

    // Highlight donut slice
    const idx = qualityData.findIndex(q => q.quality === qualityName);
    if(idx >= 0) {
        donutChart.setDatasetVisibility(0, true);
        donutChart.update();
    }
}

/* ── Monthly Bar + Line Chart ───────────────────────────── */
const months  = DATA.by_month.map(m => m.month_name);
const plans   = DATA.by_month.map(m => m.plan);
const actuals = DATA.by_month.map(m => m.actual);
const achieves= DATA.by_month.map(m => m.achieve);

const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');

new Chart(monthlyCtx, {
    data: {
        labels:   months,
        datasets: [
            {
                type:            'bar',
                label:           'Plan',
                data:            plans,
                backgroundColor: 'rgba(203,213,225,0.8)',
                borderRadius:    6,
                borderSkipped:   false,
                order:           2,
                yAxisID:         'y',
            },
            {
                type:            'bar',
                label:           'Actual',
                data:            actuals,
                backgroundColor: 'rgba(27,42,138,0.85)',
                borderRadius:    6,
                borderSkipped:   false,
                order:           1,
                yAxisID:         'y',
            },
            {
                type:         'line',
                label:        'Achieve to Plans (%)',
                data:         achieves,
                borderColor:  '#F59E0B',
                backgroundColor: 'rgba(245,158,11,.15)',
                borderWidth:  2.5,
                pointRadius:  5,
                pointHoverRadius: 8,
                pointBackgroundColor: '#F59E0B',
                pointBorderColor:     '#ffffff',
                pointBorderWidth:     2,
                tension:      0.4,
                fill:         false,
                order:        0,
                yAxisID:      'y2',
            },
        ]
    },
    options: {
        responsive:          true,
        maintainAspectRatio: false,
        interaction:         { mode: 'index', intersect: false },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#0F172A',
                padding:         14,
                cornerRadius:    10,
                titleFont:       { family: 'Inter', size: 12, weight: '700' },
                bodyFont:        { family: 'Inter', size: 11 },
                callbacks: {
                    label: ctx => {
                        if(ctx.dataset.yAxisID === 'y2') {
                            return ` Achieve: ${ctx.parsed.y}%`;
                        }
                        return ` ${ctx.dataset.label}: ${(ctx.parsed.y/1000).toFixed(1)}K ton`;
                    }
                }
            },
            // Data labels on bars
            datalabels: false,
        },
        scales: {
            x: {
                grid:  { color: 'rgba(148,163,184,.12)' },
                ticks: { font: { family: 'Inter', size: 11 }, color: '#64748B' },
            },
            y: {
                position: 'left',
                grid:     { color: 'rgba(148,163,184,.12)' },
                ticks: {
                    font:     { family: 'Inter', size: 10 },
                    color:    '#94A3B8',
                    callback: v => (v >= 1000 ? (v/1000).toFixed(0)+'K' : v),
                },
                title: { display:true, text:'Tonnage', font:{size:9}, color:'#94A3B8' },
            },
            y2: {
                position: 'right',
                grid:     { drawOnChartArea: false },
                min:      0,
                max:      200,
                ticks: {
                    font:     { family: 'Inter', size: 10 },
                    color:    '#F59E0B',
                    callback: v => v + '%',
                    stepSize: 50,
                },
                title: { display:true, text:'Achieve %', font:{size:9}, color:'#F59E0B' },
            },
        },
        animation: { duration: 1200, easing: 'easeInOutQuart' },
    },
    plugins: [{
        // Label di atas bar actual yang punya data
        afterDatasetsDraw(chart) {
            const { ctx } = chart;
            const dataset = chart.data.datasets[2]; // achieve line
            chart.getDatasetMeta(2).data.forEach((point, i) => {
                const v = achieves[i];
                if(v <= 0) return;
                ctx.save();
                ctx.font = '700 10px Inter';
                ctx.fillStyle = '#F59E0B';
                ctx.textAlign  = 'center';
                ctx.fillText(v + '%', point.x, point.y - 10);
                ctx.restore();
            });
        }
    }]
});

/* ── Realtime via Echo ───────────────────────────────────── */
if(window.Echo) {
    window.Echo.channel(`third-party.${YEAR}`)
        .listen('.ThirdPartyUpdated', e => {
            if(e.payload) applyUpdate(e.payload);
        });
}

// Polling fallback 5 menit
setInterval(() => {
    fetch(`/api/third-party/data?year=${YEAR}`, {
        headers: { 'X-Requested-With':'XMLHttpRequest', Accept:'application/json' }
    }).then(r => r.json()).then(applyUpdate).catch(console.error);
}, 300_000);

function applyUpdate(data) {
    // Update KPI
    const achEl = document.getElementById('kpi-achieve');
    if(achEl) {
        achEl.textContent = data.kpi.ytd_achieve + '%';
        achEl.style.color = data.kpi.ytd_achieve >= 100 ? '#1D9E75'
                          : data.kpi.ytd_achieve >= 80  ? '#1B2A8A' : '#F59E0B';
    }
    const actEl = document.getElementById('kpi-actual');
    if(actEl) actEl.textContent = data.kpi.sum_actual_k;
    console.log('Dashboard updated:', data.last_update);
}

/* ── Export ──────────────────────────────────────────────── */
async function exportReport(format) {
    const el     = document.getElementById('report-canvas');
    const canvas = await html2canvas(el, {
        scale:2, useCORS:true, logging:false,
        backgroundColor: format === 'jpg' ? '#FFFFFF' : '#EFF4FB',
    });
    const a = document.createElement('a');
    a.href     = canvas.toDataURL(format === 'jpg' ? 'image/jpeg' : 'image/png', 0.92);
    a.download = `ITM_3rdParty_${YEAR}.${format}`;
    a.click();
}
</script>
</body>
</html>