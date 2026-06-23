@extends ('layouts.app')
@section('title','Summary Shipment')
@section('page-title', 'Shipment Dashboard')

@push('head')
 <style>
    /* ════ RESET & BASE ════ */
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Inter',sans-serif;background:#EFF4FB;min-height:100vh;color:#1e293b}

    /* ════ ANIMATIONS ════ */
    @keyframes fadeUp   {from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
    @keyframes fadeIn   {from{opacity:0}to{opacity:1}}
    @keyframes slideL   {from{opacity:0;transform:translateX(-16px)}to{opacity:1;transform:translateX(0)}}
    @keyframes slideR   {from{opacity:0;transform:translateX(16px)}to{opacity:1;transform:translateX(0)}}
    @keyframes countUp  {from{opacity:0;transform:scale(.8)}to{opacity:1;transform:scale(1)}}
    @keyframes pulse    {0%,100%{opacity:1}50%{opacity:.4}}
    @keyframes barGrow  {from{width:0}to{width:var(--w)}}
    @keyframes floatY   {0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
    @keyframes spin     {to{transform:rotate(360deg)}}
    @keyframes shimmer  {0%{background-position:-200% 0}100%{background-position:200% 0}}

    /* ════ HEADER ════ */
    .sh-header{
        position:static;top:0;z-index:100;
        background:linear-gradient(135deg,#0D1B5E 0%,#1B2A8A 55%,#2563EB 100%);
        border-bottom:1px solid rgba(255,255,255,.1);
    }
    .sh-hd-top{
        max-width:1700px;margin:0 auto;padding:0 20px;
        height:60px;display:flex;align-items:center;justify-content:space-between;gap:12px;
    }

    /* Brand */
    .sh-brand{display:flex;align-items:center;gap:10px;flex-shrink:0}
    .sh-brand-ico{
        width:36px;height:36px;
        background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.2);
        border-radius:10px;display:flex;align-items:center;justify-content:center;
        animation:floatY 4s ease-in-out infinite;
    }
    .sh-brand-ico svg{width:20px;height:20px;stroke:white;fill:none;stroke-width:1.5}
    .sh-brand-title{font-size:13px;font-weight:900;color:white;letter-spacing:-.01em}
    .sh-brand-sub{font-size:9px;color:rgba(255,255,255,.5);margin-top:1px}

    /* Month tabs — scrollable on mobile */
    .month-tabs{
        display:flex;gap:3px;
        background:rgba(0,0,0,.25);padding:4px;border-radius:12px;
        overflow-x:auto;flex-shrink:1;min-width:0;
        scrollbar-width:none;
    }
    .month-tabs::-webkit-scrollbar{display:none}
    .month-tab{
        padding:5px 14px;border-radius:9px;font-size:11px;font-weight:700;
        cursor:pointer;transition:all .2s cubic-bezier(.34,1.56,.64,1);
        border:none;font-family:'Inter',sans-serif;
        color:rgba(255,255,255,.6);background:transparent;white-space:nowrap;flex-shrink:0;
    }
    .month-tab.active{background:white;color:#1B2A8A;box-shadow:0 2px 10px rgba(0,0,0,.2)}
    .month-tab:not(.active):hover{background:rgba(255,255,255,.15);color:white}

    /* Controls */
    .sh-controls{display:flex;align-items:center;gap:6px;flex-shrink:0}
    .dt-select{
        padding:4px 8px;font-size:11px;font-weight:600;
        background:rgba(255,255,255,.12);color:white;
        border:1px solid rgba(255,255,255,.2);border-radius:8px;
        outline:none;font-family:'Inter',sans-serif;cursor:pointer;
    }
    .dt-select option{background:#1B2A8A;color:white}
    .exp-btn{
        display:flex;align-items:center;gap:3px;
        padding:4px 10px;border-radius:8px;font-size:10px;font-weight:700;
        cursor:pointer;transition:all .15s;font-family:'Inter',sans-serif;
    }
    .exp-btn svg{width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2}
    .exp-png{background:rgba(255,255,255,.15);color:white;border:1px solid rgba(255,255,255,.25)}
    .exp-png:hover{background:rgba(255,255,255,.28);transform:translateY(-1px)}
    .exp-jpg{background:white;color:#1B2A8A;border:none}
    .exp-jpg:hover{background:#EFF4FB;transform:translateY(-1px);box-shadow:0 3px 10px rgba(0,0,0,.15)}

    /* ════ PAGE CONTENT ════ */
    .sh-main{max-width:1700px;margin:0 auto;padding:16px 20px 40px}

    /* Report title bar */
    .report-bar{
        display:flex;align-items:flex-start;justify-content:space-between;
        flex-wrap:wrap;gap:10px;margin-bottom:14px;
        animation:fadeUp .5s ease both;
    }
    .report-title{
        font-size:clamp(16px,2.5vw,24px);font-weight:900;
        background:linear-gradient(135deg,#1B2A8A,#2563EB);
        -webkit-background-clip:text;-webkit-text-fill-color:transparent;
        background-clip:text;text-transform:uppercase;letter-spacing:.02em;
    }
    .report-sub{
        display:flex;align-items:center;gap:5px;
        font-size:11px;color:#64748b;font-weight:500;margin-top:4px;
    }
    .live-dot{
        width:7px;height:7px;border-radius:50%;
        background:#10B981;animation:pulse 2s infinite;flex-shrink:0;
    }

    /* ════ TABS PANEL ════ */
    .tab-panel{display:none}
    .tab-panel.active{display:block;animation:fadeIn .3s ease}

    /* ════ THREE-COLUMN LAYOUT ════ */
    .sh-layout{
        display:grid;
        grid-template-columns:1fr 1fr 360px;
        gap:14px;align-items:start;
    }
    @media(max-width:1300px){.sh-layout{grid-template-columns:1fr 1fr}}
    @media(max-width:900px) {.sh-layout{grid-template-columns:1fr}}

    /* ════ CARDS ════ */
    .sh-card{
        background:white;border-radius:18px;overflow:hidden;
        box-shadow:0 2px 14px rgba(27,42,138,.09);
        border:1px solid rgba(27,42,138,.06);
        animation:fadeUp .5s ease both;transition:box-shadow .2s;
    }
    .sh-card:hover{box-shadow:0 6px 28px rgba(27,42,138,.14)}
    .sh-card-hd{
        padding:12px 16px;display:flex;align-items:center;
        justify-content:space-between;position:relative;overflow:hidden;
    }
    .sh-card-hd::before{
        content:'';position:absolute;inset:0;opacity:.08;
        background-image:linear-gradient(rgba(255,255,255,.5) 1px,transparent 1px),
                         linear-gradient(90deg,rgba(255,255,255,.5) 1px,transparent 1px);
        background-size:18px 18px;
    }
    .sh-card-hd.navy{background:linear-gradient(135deg,#1B2A8A,#2563EB)}
    .sh-card-hd.teal{background:linear-gradient(135deg,#0F6E56,#1D9E75)}
    .sh-card-hd h2{
        font-size:11px;font-weight:800;color:white;
        text-transform:uppercase;letter-spacing:.06em;margin:0;position:relative;
    }
    .sh-card-hd .hd-meta{font-size:10px;color:rgba(255,255,255,.75);font-weight:600;position:relative}

    /* ════ TABLE ════ */
    .tbl-wrap{overflow-x:auto;max-height:460px;overflow-y:auto}
    .tbl-wrap::-webkit-scrollbar{width:4px;height:4px}
    .tbl-wrap::-webkit-scrollbar-thumb{background:#CBD5E1;border-radius:999px}
    .sh-tbl{width:100%;border-collapse:collapse;font-size:11px}
    .sh-tbl thead th{
        padding:7px 9px;background:#f8fafc;
        font-size:9px;font-weight:700;color:#64748b;
        text-transform:uppercase;letter-spacing:.06em;
        border-bottom:2px solid #e2e8f0;white-space:nowrap;
        position:sticky;top:0;z-index:1;text-align:left;
    }
    .sh-tbl tbody tr{
        border-bottom:.5px solid #f1f5f9;transition:background .1s;
    }
    .sh-tbl tbody tr:nth-child(even){background:#fafbff}
    .sh-tbl tbody tr:hover{background:#EFF4FB}
    .sh-tbl tbody td{padding:6px 9px;color:#374151;white-space:nowrap}
    .sh-tbl tfoot td{
        padding:7px 9px;font-weight:800;font-size:11px;
        background:linear-gradient(90deg,#EFF4FB,#f0f4ff);
        color:#1B2A8A;border-top:2px solid #BFDBFE;
    }
    .td-num{text-align:right;font-variant-numeric:tabular-nums;font-weight:600}
    .td-user{max-width:160px;overflow:hidden;text-overflow:ellipsis;font-weight:600}
    .td-no{color:#94a3b8;font-weight:700;font-size:10px}

    /* Status badge */
    .s-badge{
        display:inline-flex;align-items:center;gap:2px;
        padding:2px 7px;border-radius:999px;
        font-size:8px;font-weight:800;color:white;white-space:nowrap;
    }
    .s-badge::before{
        content:'';width:4px;height:4px;border-radius:50%;
        background:rgba(255,255,255,.6);
    }

    /* ════ RIGHT PANEL — KPI ════ */
    .kpi-panel{display:flex;flex-direction:column;gap:12px}

    /* KPI header row: ITM | BOCT | MAHAKAM */
    .kpi-cols-hd{
        display:grid;grid-template-columns:repeat(3,1fr);
        gap:1px;background:#e2e8f0;border-radius:14px;overflow:hidden;
        box-shadow:0 2px 10px rgba(27,42,138,.08);
    }
    .kpi-col-hd{
        padding:10px 8px;text-align:center;background:white;
        font-size:11px;font-weight:900;color:#1B2A8A;
        text-transform:uppercase;letter-spacing:.05em;
    }
    .kpi-col-hd.navy{background:#1B2A8A;color:white}
    .kpi-col-hd.blue{background:#2563EB;color:white}
    .kpi-col-hd.teal{background:#0F6E56;color:white}

    /* Throughput block */
    .throughput-block{
        background:white;border-radius:14px;
        box-shadow:0 2px 10px rgba(27,42,138,.08);
        overflow:hidden;
    }
    .tp-row{
        display:grid;grid-template-columns:repeat(3,1fr);
        gap:1px;background:#f1f5f9;
    }
    .tp-cell{
        padding:14px 10px;text-align:center;background:white;
        transition:background .15s;
    }
    .tp-cell:hover{background:#EFF4FB}
    .tp-label{font-size:9px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em}
    .tp-value{
        font-size:20px;font-weight:900;color:#1B2A8A;margin-top:4px;
        animation:countUp .5s ease both;
    }
    .tp-value.teal{color:#059669}
    .tp-value.blue{color:#2563EB}
    .tp-unit{font-size:9px;color:#94a3b8}

    /* Shipment type blocks */
    .stype-block{
        background:white;border-radius:14px;
        box-shadow:0 2px 10px rgba(27,42,138,.08);overflow:hidden;
    }
    .stype-hd{
        padding:8px 14px;background:#f8fafc;
        border-bottom:1px solid #f1f5f9;
        display:flex;align-items:center;gap:6px;
    }
    .stype-hd-txt{font-size:10px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.05em}
    .stype-row{
        display:grid;grid-template-columns:repeat(3,1fr);
        gap:1px;background:#f1f5f9;
    }
    .stype-cell{
        padding:12px 10px;text-align:center;background:white;
        transition:background .15s;
    }
    .stype-cell:hover{background:#EFF4FB}
    .stype-num{font-size:22px;font-weight:900;color:#1B2A8A;line-height:1;animation:countUp .6s ease both}
    .stype-ico{
        font-size:22px;margin-top:4px;
        animation:floatY 3s ease-in-out infinite;
        display:block;text-align:center;
    }

    /* ════ COMPANY BAR CHART ════ */
    .company-chart-card{
        background:white;border-radius:18px;padding:18px 20px;
        box-shadow:0 2px 14px rgba(27,42,138,.09);
        margin-top:14px;animation:fadeUp .6s ease both;
    }
    .cc-title{
        font-size:11px;font-weight:800;color:#475569;
        text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px;
        display:flex;align-items:center;gap:6px;
    }
    .cc-title::before{
        content:'';width:3px;height:14px;border-radius:999px;
        background:linear-gradient(#1B2A8A,#1D9E75);flex-shrink:0;
    }
    .bar-list{display:flex;flex-direction:column;gap:8px}
    .bar-item{display:flex;align-items:center;gap:10px}
    .bar-label{
        width:50px;font-size:11px;font-weight:800;color:#1B2A8A;
        text-align:right;flex-shrink:0;
    }
    .bar-track{
        flex:1;height:22px;background:#f1f5f9;border-radius:6px;overflow:hidden;
        position:relative;
    }
    .bar-fill{
        height:100%;border-radius:6px;
        width:0;transition:width 1.2s cubic-bezier(.34,1,.64,1);
        display:flex;align-items:center;padding-left:8px;
        position:relative;
    }
    .bar-fill::after{
        content:attr(data-val);
        font-size:10px;font-weight:800;color:white;
        position:absolute;left:8px;white-space:nowrap;
    }
    .bar-total{
        font-size:11px;font-weight:700;color:#64748b;
        width:60px;text-align:right;flex-shrink:0;
    }
    /* Grand total next to chart */
    .chart-grand-total{
        font-size:28px;font-weight:900;color:#1B2A8A;margin-bottom:4px;
    }

    /* ════ NO DATA ════ */
    .no-data{
        text-align:center;padding:80px 20px;background:white;
        border-radius:18px;margin-top:16px;
    }
    .no-data-ico{
        width:60px;height:60px;background:#EFF4FB;border-radius:18px;
        display:flex;align-items:center;justify-content:center;
        margin:0 auto 16px;animation:floatY 3s ease-in-out infinite;
    }

    /* ════ RESPONSIVE ════ */
    @media(max-width:640px){
        .sh-hd-top{height:auto;padding:10px 14px;flex-wrap:wrap;gap:8px}
        .month-tabs{width:100%;order:3}
        .sh-controls{order:2;gap:4px}
        .sh-card-hd h2{font-size:10px}
        .sh-tbl{font-size:10px}
        .sh-tbl td,.sh-tbl th{padding:5px 6px}
        .tp-value{font-size:16px}
        .stype-num{font-size:18px}
        .report-title{font-size:15px}
    }
    </style>
@endpush

@section('content')
<header class="sh-header">
    <div class="sh-hd-top">
        <!-- Brand -->
        <div class="sh-brand">
            <a href="{{ route('dashboard.loading') }}"
               style="display:flex;align-items:center;gap:4px;text-decoration:none;
                      color:rgba(255,255,255,.65);font-size:11px;font-weight:600"
               onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,.65)'">
                <svg style="width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                </svg>
            </a>
            <div style="width:1px;height:20px;background:rgba(255,255,255,.2)"></div>
            <div class="sh-brand-ico">
                <svg viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
            </div>
            <div>
                <p class="sh-brand-title">Summary Shipment</p>
                <p class="sh-brand-sub" id="brand-sub">
                    {{ $data['has_data'] ? 'Next Month Planning' : 'No Data' }}
                </p>
            </div>
        </div>

        <!-- Month tabs -->
        <nav class="month-tabs" id="month-tabs">
            @if($data['has_data'])
                @foreach($data['months'] as $mn => $month)
                <button class="month-tab {{ $mn == $data['default_month'] ? 'active' : '' }}"
                        onclick="switchMonth({{ $mn }}, this)"
                        data-month="{{ $mn }}">
                    {{ $month['month_label'] }}
                </button>
                @endforeach
            @endif
        </nav>

        <!-- Controls -->
        <div class="sh-controls">
            <div style="display:flex;align-items:center;gap:4px;font-size:10px;color:rgba(255,255,255,.8);font-weight:600">
                <span class="live-dot"></span> Live
            </div>
            @if(count($availableDates ?? []) > 0)
            <select class="dt-select" onchange="window.location.href='/shipment?date='+this.value">
                @foreach($availableDates as $d)
                <option value="{{ $d['date'] }}" {{ $d['date'] === $selectedDate ? 'selected' : '' }}>
                    {{ $d['label'] }}
                </option>
                @endforeach
            </select>
            @endif
            <!-- <button class="exp-btn exp-png" onclick="exportReport('png')">
                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                PNG
            </button>
            <button class="exp-btn exp-jpg" onclick="exportReport('jpg')">
                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                JPG
            </button> -->
        </div>
    </div>
</header>

<!-- ═══════ MAIN ═══════ -->
<main class="sh-main" id="report-canvas">
@if(!$data['has_data'])
<div class="no-data">
    <div class="no-data-ico">
        <svg style="width:28px;height:28px;stroke:#1B2A8A;fill:none;stroke-width:1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
        </svg>
    </div>
    <p style="font-size:15px;font-weight:800;color:#1B2A8A;margin-bottom:6px">Belum ada data shipment</p>
    <p style="font-size:13px;color:#94a3b8">
        Upload file Excel di <a href="/admin" style="color:#1B2A8A;font-weight:700;text-decoration:none">Admin Panel</a>.
    </p>
</div>
@else

<!-- Report bar -->
<div class="report-bar">
    <div>
        <p class="report-title" id="report-heading">
            ITM {{ strtoupper($data['months'][$data['default_month']]['month_label'] ?? '') }} SUMMARY SHIPMENT
        </p>
        <div class="report-sub">
            <span class="live-dot"></span>
            LAST UPDATE ON {{ strtoupper($data['snapshot']['upload_date'] ?? '') }}
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:8px">
        <div style="background:white;border:1px solid #BFDBFE;border-radius:10px;
                    padding:6px 12px;font-size:10px;font-weight:600;color:#1B2A8A;
                    display:flex;align-items:center;gap:5px">
            <svg style="width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Snapshot: {{ $data['snapshot']['upload_date'] }}
        </div>
        <img src="{{ asset('images/logos/itm.png') }}" alt="ITM"
             style="height:34px;object-fit:contain" onerror="this.style.display='none'">
    </div>
</div>


<!-- ════ MONTH PANELS ════ -->
@foreach($data['months'] as $mn => $month)
<div class="tab-panel {{ $mn == $data['default_month'] ? 'active' : '' }}" id="panel-month-{{ $mn }}">

    <!-- Three-column layout -->
    <div class="sh-layout">

        <!-- COL 1: MAHAKAM TABLE -->
        <div class="sh-card" style="animation-delay:.04s">
            <div class="sh-card-hd teal">
                <h2>
                    <span style="display:flex;align-items:center;gap:5px;position:relative">
                        <svg style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25"/>
                        </svg>
                        MAHAKAM
                    </span>
                </h2>
                <span class="hd-meta">{{ count($month['mahakam_rows']) }} rows · {{ number_format($month['mahakam_total']) }}</span>
            </div>
            <div class="tbl-wrap">
                <table class="sh-tbl">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>End user</th>
                            <th>TS(AR)</th>
                            <th>CV(AR)</th>
                            <th>CV(NAR)</th>
                            <th>Total</th>
                            <th>%&nbsp;Shpr</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($month['mahakam_rows'] as $idx => $row)
                        <tr style="animation:slideL .35s ease {{ $idx * 0.02 }}s both">
                            <td class="cell-no">{{ $loop->iteration }}</td>
                            <td class="td-user">{{ $row['end_user'] }}</td>
                            <td class="td-num">{{ $row['ts_ar'] !== null ? number_format($row['ts_ar'],2) : '—' }}</td>
                            <td class="td-num">{{ $row['cv_ar']  !== null ? number_format($row['cv_ar'])  : '—' }}</td>
                            <td class="td-num">{{ $row['cv_nar'] !== null ? number_format($row['cv_nar']) : '—' }}</td>
                            <td class="td-num" style="color:#059669;font-weight:800">{{ number_format($row['total']) }}</td>
                            <td>
                                <span class="s-badge"
                                      style="background:{{ $row['pct_shipper'] >= 50 ? '#EC4899' : ($row['pct_shipper'] > 0 ? '#F59E0B' : '#94a3b8') }}">
                                    {{ $row['pct_shipper'] }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" style="text-align:right;color:#059669">Total</td>
                            <td class="td-num" style="color:#059669">{{ number_format($month['mahakam_total']) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- COL 2: BOCT TABLE -->
        <div class="sh-card" style="animation-delay:.07s">
            <div class="sh-card-hd navy">
                <h2>
                    <span style="display:flex;align-items:center;gap:5px;position:relative">
                        <svg style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625z"/>
                        </svg>
                        BOCT
                    </span>
                </h2>
                <span class="hd-meta">{{ count($month['boct_rows']) }} rows · {{ number_format($month['boct_total']) }}</span>
            </div>
            <div class="tbl-wrap">
                <table class="sh-tbl">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>End user</th>
                            <th>TS(AR)</th>
                            <th>CV(AR)</th>
                            <th>CV(NAR)</th>
                            <th>Total</th>
                            <th>%&nbsp;Shpr</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($month['boct_rows'] as $idx => $row)
                        <tr style="animation:slideR .35s ease {{ $idx * 0.02 }}s both">
                            <td class="td-no">{{ $row['no'] }}</td>
                            <td class="td-user">{{ $row['end_user'] }}</td>
                            <td class="td-num">{{ $row['ts_ar'] !== null ? number_format($row['ts_ar'],2) : '—' }}</td>
                            <td class="td-num">{{ $row['cv_ar']  !== null ? number_format($row['cv_ar'])  : '—' }}</td>
                            <td class="td-num">{{ $row['cv_nar'] !== null ? number_format($row['cv_nar']) : '—' }}</td>
                            <td class="td-num" style="color:#1B2A8A;font-weight:800">{{ number_format($row['total']) }}</td>
                            <td>
                                <span class="s-badge"
                                      style="background:{{ $row['pct_shipper'] >= 50 ? '#EC4899' : ($row['pct_shipper'] > 0 ? '#F59E0B' : '#94a3b8') }}">
                                    {{ $row['pct_shipper'] }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" style="text-align:right;color:#1B2A8A">Total</td>
                            <td class="td-num" style="color:#1B2A8A">{{ number_format($month['boct_total']) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- COL 3: KPI PANEL -->
        <div class="kpi-panel">

            <!-- Column headers -->
            <div class="kpi-cols-hd">
                <div class="kpi-col-hd navy">ITM</div>
                <div class="kpi-col-hd blue">BOCT</div>
                <div class="kpi-col-hd teal">MAHAKAM</div>
            </div>

            <!-- Total Throughput -->
            <div class="throughput-block">
                <div style="padding:8px 14px;background:#f8fafc;border-bottom:1px solid #f1f5f9">
                    <p style="font-size:10px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.06em">
                        Total Throughput
                    </p>
                </div>
                <div class="tp-row">
                    @foreach([
                        [$month['kpi']['itm']['total'],  '#1B2A8A'],
                        [$month['kpi']['boct']['total'], '#2563EB'],
                        [$month['kpi']['mah']['total'],  '#059669'],
                    ] as [$val, $color])
                    <div class="tp-cell">
                        <p class="tp-value" style="color:{{ $color }}">
                            {{ $val >= 1000000
                                ? round($val/1000000,2).'M'
                                : ($val >= 1000 ? round($val/1000,1).'K' : number_format($val)) }}
                        </p>
                        <p class="tp-unit">Ton</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Total Vessel -->
            <div class="stype-block">
                <div class="stype-hd">
                    <span style="font-size:16px"></span>
                    <span class="stype-hd-txt">Total Vessel</span>
                </div>
                <div class="stype-row">
                    @foreach([
                        [$month['kpi']['itm']['vessel'],  '#1B2A8A'],
                        [$month['kpi']['boct']['vessel'], '#2563EB'],
                        [$month['kpi']['mah']['vessel'],  '#059669'],
                    ] as [$val, $color])
                    <div class="stype-cell">
                        <p class="stype-num" style="color:{{ $color }}">{{ $val }}</p>
                        <span class="stype-ico">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"><path d="M.75 23.25a5.75 5.75 0 0 0 4.5-2c1.5 1.5 1.75 2 3.25 2s2-.5 3.5-2c1.5 1.5 2 2 3.5 2s1.75-.5 3.25-2a5.75 5.75 0 0 0 4.5 2m-.37-3.05l.26-2.11a.71.71 0 0 0-.14-.59a.73.73 0 0 0-.56-.25H1.5a.76.76 0 0 0-.75.82a8.2 8.2 0 0 0 .54 2.26"/><path d="M21.75 17.25h-6v-12a1.5 1.5 0 0 1 1.5-1.5h3a1.5 1.5 0 0 1 1.5 1.5zm-3-10h-3m3.75-3.5v-3m-15.75 8h12v8.5H2.25v-7a1.5 1.5 0 0 1 1.5-1.5m4.5 0v8.5m-6-4.25h13.24"/></g></svg>                        
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Total Direct Shipment -->
            <div class="stype-block">
                <div class="stype-hd">
                    <span style="font-size:16px"></span>
                    <span class="stype-hd-txt">Total Direct Shipment</span>
                </div>
                <div class="stype-row">
                    @foreach([
                        [$month['kpi']['itm']['direct'],  '#1B2A8A'],
                        [$month['kpi']['boct']['direct'], '#2563EB'],
                        [$month['kpi']['mah']['direct'],  '#059669'],
                    ] as [$val, $color])
                    <div class="stype-cell">
                        <p class="stype-num" style="color:{{ $color }}">{{ $val }}</p>
                        <span class="stype-ico">
                            <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24"><path fill="currentColor" d="M10.288 21.775q-.813-.2-1.813-.675q-1.175.475-2.237.65T4 21.975q-.425.025-.712-.262T3 21t.288-.712t.712-.313q.6-.025 1.113-.075t1.012-.162t1.025-.288t1.125-.425q.125-.05.25-.05t.25.05q.825.425 1.513.7T12 20t1.713-.275t1.512-.7q.125-.05.25-.05t.25.05q.6.25 1.125.425t1.025.288t1.025.162t1.1.075q.425.025.713.313T21 21t-.288.713t-.712.262q-1.175-.05-2.238-.225t-2.212-.65q-1 .475-1.825.675t-1.725.2t-1.712-.2M12 18q-1.5 0-2.625-1L8.25 16q-.475.45-1.012.813t-1.113.637q-.475.2-.937-.05t-.613-.75L2.825 11q-.125-.425.075-.775t.625-.475L5 9.35V6q0-.825.588-1.412T7 4h2.5V3q0-.425.288-.712T10.5 2h3q.425 0 .713.288T14.5 3v1H17q.825 0 1.413.588T19 6v3.35l1.475.4q.425.125.625.475t.075.775l-1.75 5.65q-.15.5-.612.75t-.938.05q-.6-.275-1.137-.638T15.75 16l-1.125 1Q13.5 18 12 18M7 6v2.825l4.5-1.2q.25-.075.5-.075t.5.075l4.5 1.2V6zm5 3.575L5.05 11.4l1.15 3.725q.375-.3.713-.613t.687-.662q.3-.325.738-.312t.712.362q.575.675 1.3 1.388t1.7.712q.95 0 1.65-.725t1.275-1.375q.275-.35.713-.363t.737.313q.35.35.688.663t.712.612l1.15-3.725zm.025 3.2"/></svg>                        
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Total Dump Truck -->
            <div class="stype-block">
                <div class="stype-hd">
                    <span style="font-size:16px"></span>
                    <span class="stype-hd-txt">Total Dump Truck</span>
                </div>
                <div class="stype-row">
                    @foreach([
                        [$month['kpi']['itm']['dump'],  '#1B2A8A'],
                        [$month['kpi']['boct']['dump'], '#2563EB'],
                        [$month['kpi']['mah']['dump'],  '#059669'],
                    ] as [$val, $color])
                    <div class="stype-cell">
                        <p class="stype-num" style="color:{{ $color }}">{{ $val }}</p>
                        <span class="stype-ico">
                            <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 512 512"><path fill="currentColor" d="M102.5 70.4c-.8 0-1.7.1-2.5.22c-30.99 5.31-62.08 74.08-72.4 98.98h226.8l11.9-23.9c-12.4-20-35.3-50.36-58.3-49.08c-15.1.8-44 33.98-44 33.98s-35.4-60.51-61.5-60.2m195.1 53.2l-32 64h-79.7l-40.7 95c22 3.3 41.4 14.7 55 31h87.6c4.8-5.8 10.3-10.9 16.4-15.3l28.6-128.7h48.9l16.3-46zM21 187.6v80l13.57 3.5l35.8-83.5zm68.91 0l-37.77 88.1l25.56 6.7l40.6-94.8zm47.99 0L95.28 287l3.7 1c8.42-3.4 17.52-5.6 27.02-6.2l40.3-94.2zm209.3 0l-22.1 99.5c9.6-3.5 20.1-5.5 30.9-5.5c40.3 0 74.6 27.1 85.4 64H491v-80.5l-46.5-15.5l-15.5-62h-34.7zm17.8 14h46l12.5 50h-71l10.8-43.2zm-233 98c-39.32 0-71 31.7-71 71s31.68 71 71 71c39.3 0 71-31.7 71-71s-31.7-71-71-71m224 0c-39.3 0-71 31.7-71 71s31.7 71 71 71s71-31.7 71-71s-31.7-71-71-71m-320.62 32l-12.4 62h23.05c-1.97-7.3-3.03-15.1-3.03-23c0-14 3.25-27.2 9.04-39zm176.62 0c5.7 11.8 9 25 9 39c0 7.9-1.1 15.7-3 23h52c-1.9-7.3-3-15.1-3-23c0-14 3.3-27.2 9-39zm-80 7a32 32 0 0 1 32 32a32 32 0 0 1-32 32a32 32 0 0 1-32-32a32 32 0 0 1 32-32m224 0a32 32 0 0 1 32 32a32 32 0 0 1-32 32a32 32 0 0 1-32-32a32 32 0 0 1 32-32m88.7 25c.2 2.3.3 4.6.3 7c0 10.7-1.9 20.9-5.4 30.5l51.4-20.6v-16.9z"/></svg>
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Company bar chart -->
    @if(count($month['chart_company']['labels']) > 0)
    <div class="company-chart-card">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:4px;flex-wrap:wrap;gap:8px">
            <p class="cc-title">Total Tonnages by Company</p>
            <p class="chart-grand-total">{{ number_format($month['grand_total']) }}</p>
        </div>
        <div class="bar-list" id="bars-month-{{ $mn }}">
            @php
                $maxVal = max($month['chart_company']['data']);
            @endphp
            @foreach($month['chart_company']['labels'] as $i => $co)
            @php
                $val = $month['chart_company']['data'][$i];
                $color = $month['chart_company']['colors'][$i];
                $pct = $maxVal > 0 ? ($val / $maxVal * 100) : 0;
                $displayVal = $val >= 1000000
                    ? round($val/1000000,2).'M'
                    : ($val >= 1000 ? round($val/1000).'K' : number_format($val));
            @endphp
            <div class="bar-item" style="animation:fadeUp .5s ease {{ $i * 0.08 }}s both">
                <span class="bar-label">{{ $co }}</span>
                <div class="bar-track">
                    <div class="bar-fill"
                         style="background:{{ $color }};--w:{{ $pct }}%"
                         data-val="{{ $displayVal }}"
                         data-pct="{{ $pct }}">
                    </div>
                </div>
                <span class="bar-total">{{ $displayVal }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endforeach

<!-- Footer -->
<div style="display:flex;align-items:center;justify-content:space-between;
            padding:10px 2px;font-size:9px;color:#94a3b8;margin-top:8px;flex-wrap:wrap;gap:6px">
    <span><strong style="color:#64748b">ITM Summary Shipment Dashboard</strong> · Confidential</span>
    <span>Generated {{ now()->format('d M Y H:i') }}</span>
</div>

@endif
</main>
@push('scripts')
<script>
const DATA = @json($data);
let currentMonth = {{ $data['default_month'] ?? 1 }};

/* ── Month tab switching ── */
function switchMonth(mn, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.month-tab').forEach(b => b.classList.remove('active'));
    const panel = document.getElementById('panel-month-' + mn);
    if (panel) panel.classList.add('active');
    if (btn) btn.classList.add('active');
    currentMonth = mn;

    // Update report heading
    if (DATA.has_data && DATA.months[mn]) {
        const lbl = DATA.months[mn].month_label || '';
        const heading = document.getElementById('report-heading');
        if (heading) heading.textContent = 'ITM ' + lbl.toUpperCase() + ' SUMMARY SHIPMENT';
        const sub = document.getElementById('brand-sub');
        if (sub) sub.textContent = lbl;
    }

    // Trigger bar animations for this month
    animateBars(mn);
}

/* ── Animate bar fills ── */
function animateBars(mn) {
    const container = document.getElementById('bars-month-' + mn);
    if (!container) return;

    container.querySelectorAll('.bar-fill').forEach((bar, i) => {
        const targetPct = parseFloat(bar.dataset.pct) || 0;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = targetPct + '%';
        }, i * 80 + 100);
    });
}

/* ── Init: animate default month bars ── */
document.addEventListener('DOMContentLoaded', () => {
    animateBars(currentMonth);
});

/* ── Export dengan loading overlay ── */
async function exportReport(format) {
    const overlay = document.createElement('div');
    overlay.innerHTML = `
        <div style="position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;
                    display:flex;align-items:center;justify-content:center">
            <div style="background:white;border-radius:16px;padding:28px 36px;text-align:center;
                        box-shadow:0 20px 60px rgba(0,0,0,.3)">
                <div style="width:34px;height:34px;border:3px solid #1B2A8A;border-top-color:transparent;
                            border-radius:50%;animation:spin 1s linear infinite;margin:0 auto 14px"></div>
                <p style="font-size:13px;font-weight:700;color:#1B2A8A;margin:0">
                    Mengekspor ${format.toUpperCase()}...
                </p>
                <p style="font-size:11px;color:#94a3b8;margin-top:4px">Mohon tunggu sebentar</p>
            </div>
        </div>`;
    document.body.appendChild(overlay);

    // Pastikan bar sudah teranimasikan sebelum capture
    animateBars(currentMonth);
    await new Promise(r => setTimeout(r, 800));

    try {
        const el = document.getElementById('report-canvas');
        const canvas = await html2canvas(el, {
            scale: 2,
            useCORS: true,
            logging: false,
            allowTaint: true,
            backgroundColor: format === 'jpg' ? '#FFFFFF' : '#EFF4FB',
            // Pastikan bar terlihat saat render
            onclone: (cloned) => {
                cloned.querySelectorAll('.bar-fill').forEach(bar => {
                    bar.style.transition = 'none';
                    bar.style.width = bar.dataset.pct + '%';
                });
            }
        });

        const a = document.createElement('a');
        const monthLbl = (DATA.months[currentMonth]?.month_label || 'Month'+currentMonth)
            .replace(/\s+/g,'_');
        a.href = canvas.toDataURL(format === 'jpg' ? 'image/jpeg' : 'image/png', 0.95);
        a.download = `ITM_Shipment_${monthLbl}_${new Date().toISOString().slice(0,10)}.${format}`;
        a.click();
    } finally {
        document.body.removeChild(overlay);
    }
}
</script>
@endpush
@endsection