@extends ('layouts.app')
@section('title','Summary Loading')
@section('page-title', 'Loading Dashboard')

@push('head')
<style>
/* ════════════════════════════════════════════════════
       ANIMATIONS
    ════════════════════════════════════════════════════ */
    @keyframes fadeUp    { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
    @keyframes fadeIn    { from{opacity:0} to{opacity:1} }
    @keyframes slideLeft { from{opacity:0;transform:translateX(-20px)} to{opacity:1;transform:translateX(0)} }
    @keyframes slideRight{ from{opacity:0;transform:translateX(20px)} to{opacity:1;transform:translateX(0)} }
    @keyframes scaleIn   { from{opacity:0;transform:scale(.9)} to{opacity:1;transform:scale(1)} }
    @keyframes pulse     { 0%,100%{opacity:1} 50%{opacity:.4} }
    @keyframes shimmer   {
        0%   { background-position:-200% 0 }
        100% { background-position:200% 0 }
    }
    @keyframes countUp   { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }
    @keyframes rowSlide  { from{opacity:0;transform:translateX(-8px)} to{opacity:1;transform:translateX(0)} }
    @keyframes glowPulse {
        0%,100% { box-shadow: 0 0 0 0 rgba(37,99,235,0) }
        50%     { box-shadow: 0 0 0 6px rgba(37,99,235,.12) }
    }
    @keyframes barGrow   { from{width:0} to{width:var(--w)} }
    @keyframes spin      { to{transform:rotate(360deg)} }
    @keyframes floatY    { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-6px)} }

    /* ════════════════════════════════════════════════════
       BASE
    ════════════════════════════════════════════════════ */
    * { box-sizing:border-box; margin:0; padding:0; }
    body {
        font-family: 'Inter', sans-serif;
        background: #EFF4FB;
        min-height: 100vh;
        color: #1e293b;
    }

    /* ════════════════════════════════════════════════════
       HEADER
    ════════════════════════════════════════════════════ */
    .ld-header {
        position: sticky; top: 0; z-index: 100;
        background: linear-gradient(135deg, #0D1B5E 0%, #1B2A8A 50%, #0F6E56 100%);
        border-bottom: 1px solid rgba(255,255,255,.1);
        backdrop-filter: blur(12px);
    }
    .ld-header::after {
        content: '';
        position: absolute; inset-x: 0; bottom: 0; height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.15), transparent);
    }
    .hd-inner {
        max-width: 1600px; margin: 0 auto; padding: 0 20px;
        height: 62px; display: flex; align-items: center; justify-content: space-between; gap: 16px;
    }
    .hd-brand {
        display: flex; align-items: center; gap: 12px; flex-shrink: 0;
    }
    .hd-brand-icon {
        width: 38px; height: 38px;
        background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.2);
        border-radius: 11px; display: flex; align-items: center; justify-content: center;
        animation: floatY 4s ease-in-out infinite;
    }
    .hd-brand-icon svg { width: 20px; height: 20px; stroke: white; fill: none; stroke-width: 1.5; }
    .hd-title { font-size: 13px; font-weight: 900; color: white; letter-spacing: -.01em; }
    .hd-sub   { font-size: 9px; color: rgba(255,255,255,.5); margin-top: 1px; }

    /* Tab nav */
    .tab-nav { display: flex; gap: 4px; background: rgba(0,0,0,.2); padding: 4px; border-radius: 12px; }
    .tab-btn {
        padding: 6px 18px; border-radius: 9px; font-size: 12px; font-weight: 700;
        cursor: pointer; transition: all .25s cubic-bezier(.34,1.56,.64,1);
        border: none; font-family: 'Inter',sans-serif;
        color: rgba(255,255,255,.6); background: transparent; position: relative;
    }
    .tab-btn.active {
        background: white; color: #1B2A8A;
        box-shadow: 0 2px 12px rgba(0,0,0,.2);
    }
    .tab-btn:not(.active):hover { background: rgba(255,255,255,.12); color: white; }
    .tab-btn .tab-badge {
        position: absolute; top: -4px; right: -4px;
        width: 16px; height: 16px; border-radius: 50%; font-size: 8px; font-weight: 800;
        display: flex; align-items: center; justify-content: center;
        background: #EC4899; color: white;
    }

    /* Controls */
    .hd-controls { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
    .date-select {
        padding: 5px 10px; font-size: 11px; font-weight: 600;
        background: rgba(255,255,255,.12); color: white;
        border: 1px solid rgba(255,255,255,.2); border-radius: 9px;
        outline: none; font-family: 'Inter',sans-serif; cursor: pointer;
        transition: all .2s;
    }
    .date-select:hover { background: rgba(255,255,255,.22); }
    .date-select option { background: #1B2A8A; color: white; }
    .exp-btn {
        display: flex; align-items: center; gap: 4px;
        padding: 5px 12px; border-radius: 9px; font-size: 11px; font-weight: 700;
        cursor: pointer; transition: all .2s; font-family: 'Inter',sans-serif;
    }
    .exp-btn svg { width: 13px; height: 13px; stroke: currentColor; fill: none; stroke-width: 2; }
    .exp-png { background: rgba(255,255,255,.15); color: white; border: 1px solid rgba(255,255,255,.25); }
    .exp-png:hover { background: rgba(255,255,255,.28); transform: translateY(-1px); }
    .exp-jpg { background: white; color: #1B2A8A; border: none; }
    .exp-jpg:hover { background: #EFF4FB; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,.15); }

    /* ════════════════════════════════════════════════════
       MAIN LAYOUT
    ════════════════════════════════════════════════════ */
    .ld-main { max-width: 1600px; margin: 0 auto; padding: 18px 20px 40px; }

    /* ── Report title bar ── */
    .report-title-bar {
        display: flex; align-items: flex-start; justify-content: space-between;
        flex-wrap: wrap; gap: 12px; margin-bottom: 16px;
        animation: fadeUp .5s ease both;
    }
    .report-title {
        font-size: 22px; font-weight: 900; color: #1B2A8A;
        text-transform: uppercase; letter-spacing: .02em; line-height: 1.1;
    }
    .report-subtitle {
        display: flex; align-items: center; gap: 6px;
        font-size: 11px; color: #64748b; margin-top: 5px; font-weight: 500;
    }
    .report-subtitle svg { width: 14px; height: 14px; stroke: #1D9E75; fill: none; }
    .live-dot {
        width: 7px; height: 7px; border-radius: 50%; background: #10B981;
        animation: pulse 2s infinite; flex-shrink: 0;
    }

    /* Snapshot badge */
    .snap-badge {
        display: flex; align-items: center; gap: 6px;
        background: white; border: 1px solid #BFDBFE; border-radius: 10px;
        padding: 6px 12px; font-size: 10px; font-weight: 600; color: #1B2A8A;
        box-shadow: 0 1px 8px rgba(27,42,138,.08);
    }

    /* ── Tab panels ── */
    .tab-panel { display: none; }
    .tab-panel.active { display: block; animation: fadeIn .3s ease; }

    /* ════════════════════════════════════════════════════
       GRID LAYOUT
    ════════════════════════════════════════════════════ */
    .ld-grid {
        display: grid;
        grid-template-columns: 1fr 460px;
        gap: 14px;
    }
    @media(max-width:1200px) { .ld-grid { grid-template-columns: 1fr; } }

    /* ── Left panel (tables) ── */
    .left-panel { display: flex; flex-direction: column; gap: 14px; }

    /* ── Right panel (KPIs + charts) ── */
    .right-panel { display: flex; flex-direction: column; gap: 12px; }

    /* ════════════════════════════════════════════════════
       CARDS
    ════════════════════════════════════════════════════ */
    .ld-card {
        background: white; border-radius: 20px; overflow: hidden;
        box-shadow: 0 2px 16px rgba(27,42,138,.09);
        border: 1px solid rgba(27,42,138,.06);
        transition: box-shadow .2s, transform .2s;
        animation: fadeUp .5s ease both;
    }
    .ld-card:hover {
        box-shadow: 0 6px 28px rgba(27,42,138,.14);
    }
    .card-hd {
        padding: 13px 18px; display: flex; align-items: center; justify-content: space-between;
        position: relative; overflow: hidden;
    }
    .card-hd::before {
        content: '';
        position: absolute; inset: 0; opacity: .08;
        background-image:
            linear-gradient(rgba(255,255,255,.5) 1px,transparent 1px),
            linear-gradient(90deg,rgba(255,255,255,.5) 1px,transparent 1px);
        background-size: 20px 20px;
    }
    .card-hd.navy   { background: linear-gradient(135deg,#1B2A8A,#2851A3); }
    .card-hd.teal   { background: linear-gradient(135deg,#0F6E56,#1D9E75); }
    .card-hd.blue   { background: linear-gradient(135deg,#2563EB,#1D4ED8); }
    .card-hd h2 {
        font-size: 11px; font-weight: 800; color: white;
        text-transform: uppercase; letter-spacing: .06em; margin: 0; position: relative;
    }
    .card-hd .hd-meta {
        font-size: 10px; color: rgba(255,255,255,.7); font-weight: 600; position: relative;
    }

    /* Port label badges */
    .port-badge {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 7px; margin-right: 4px;
    }
    .port-badge.boct    { background:#EFF4FB; color:#1B2A8A; }
    .port-badge.muara   { background:#ECFDF5; color:#059669; }
    .port-badge.gpk     { background:#FFFBEB; color:#D97706; }

    /* ════════════════════════════════════════════════════
       TABLE
    ════════════════════════════════════════════════════ */
    .table-scroll {
    overflow-x: auto;
    max-height: 420px;
    }

    .table-scroll::-webkit-scrollbar { width: 4px; height: 4px; }
    .table-scroll::-webkit-scrollbar-track { background: #f8fafc; }
    .table-scroll::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 999px; }
    .table-scroll::-webkit-scrollbar-thumb:hover { background: #94A3B8; }

    .ld-table { width: 100%; border-collapse: collapse; font-size: 11px; }
    .ld-table thead th {
        padding: 8px 10px; background: #f8fafc;
        font-size: 9px; font-weight: 700; color: #64748b;
        text-transform: uppercase; letter-spacing: .06em;
        border-bottom: 2px solid #e2e8f0; text-align: left; white-space: nowrap;
        position: sticky; top: 0; z-index: 1;
    }
    .ld-table tbody tr {
        border-bottom: 0.5px solid #f1f5f9;
        transition: background .1s;
    }
    .ld-table tbody tr:nth-child(even) { background: #fafbff; }
    .ld-table tbody tr:hover { background: #EFF4FB; }
    .ld-table tbody td { padding: 7px 10px; color: #374151; white-space: nowrap; }
    .ld-table tbody td.cell-num  { text-align: right; font-variant-numeric: tabular-nums; font-weight: 600; }
    .ld-table tbody td.cell-user { max-width: 180px; overflow:hidden; text-overflow:ellipsis; font-weight:600; }
    .ld-table tbody td.cell-no   { color: #94a3b8; font-weight: 700; font-size: 10px; }
    .ld-table tfoot td {
        padding: 8px 10px; font-weight: 800; font-size: 11px;
        background: linear-gradient(90deg,#EFF4FB,#f0f4ff);
        color: #1B2A8A; border-top: 2px solid #BFDBFE; white-space: nowrap;
    }

    /* Pen/Dem colored cells */
    .cell-positive { color: #059669; font-weight: 700; }
    .cell-negative { color: #DC2626; font-weight: 700; }
    .cell-neutral  { color: #94a3b8; }

    /* Status badge */
    .status-badge {
        display: inline-flex; align-items: center; gap: 3px;
        padding: 3px 9px; border-radius: 999px;
        font-size: 9px; font-weight: 800; color: white; white-space: nowrap;
    }
    .status-badge::before {
        content: ''; width: 5px; height: 5px; border-radius: 50%;
        background: rgba(255,255,255,.6);
    }

    /* ════════════════════════════════════════════════════
       KPI CARDS
    ════════════════════════════════════════════════════ */
    .kpi-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .kpi-card {
        background: white; border-radius: 15px; padding: 14px 16px;
        border: 1px solid #f1f5f9; border-left-width: 4px;
        box-shadow: 0 1px 8px rgba(27,42,138,.06);
        transition: all .25s cubic-bezier(.34,1.56,.64,1);
        cursor: default;
    }
    .kpi-card:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 6px 20px rgba(27,42,138,.14); }
    .kpi-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; }
    .kpi-value { font-size: 22px; font-weight: 900; margin: 5px 0 2px; line-height: 1; animation: countUp .6s ease both; }
    .kpi-unit  { font-size: 9px; color: #94a3b8; }

    /* ════════════════════════════════════════════════════
       CHART CARDS
    ════════════════════════════════════════════════════ */
    .chart-card {
        background: white; border-radius: 15px; padding: 14px 16px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 1px 8px rgba(27,42,138,.06);
        animation: scaleIn .5s ease both;
    }
    .chart-title {
        font-size: 10px; font-weight: 800; color: #475569;
        text-transform: uppercase; letter-spacing: .06em; margin-bottom: 12px;
        display: flex; align-items: center; gap: 6px;
    }
    .chart-title::before {
        content: ''; width: 3px; height: 14px; border-radius: 999px;
        background: linear-gradient(#1B2A8A,#1D9E75); flex-shrink: 0;
    }
    .chart-wrap { position: relative; }

    /* Legend custom */
    .chart-legend { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; justify-content: center; }
    .leg-item { display: flex; align-items: center; gap: 4px; }
    .leg-dot  { width: 8px; height: 8px; border-radius: 2px; flex-shrink: 0; }
    .leg-lbl  { font-size: 9px; color: #64748b; }

    /* ════════════════════════════════════════════════════
       WEIGHT AVG CARDS
    ════════════════════════════════════════════════════ */
    .wavg-section { padding: 14px 18px; border-top: 1px solid #f1f5f9; }
    .wavg-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .wavg-card {
        border-radius: 13px; padding: 14px 16px;
        background: #f8fafc; border: 1.5px solid #e2e8f0;
        transition: all .2s;
    }
    .wavg-card:hover { border-color: #BFDBFE; background: #EFF4FB; }
    .wavg-label { font-size: 9px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .05em; }
    .wavg-val   { font-size: 28px; font-weight: 900; margin-top: 5px; line-height: 1; }
    .wavg-val.pos  { color: #059669; }
    .wavg-val.neg  { color: #DC2626; }
    .wavg-val.zero { color: #94a3b8; }

    /* ════════════════════════════════════════════════════
       OVERALL: Split label divider
    ════════════════════════════════════════════════════ */
    .section-divider {
        display: flex; align-items: center; gap: 10px;
        font-size: 10px; font-weight: 800; color: #64748b;
        text-transform: uppercase; letter-spacing: .06em;
        margin: 4px 0;
    }
    .section-divider::before, .section-divider::after {
        content: ''; flex: 1; height: 1px; background: #e2e8f0;
    }

    /* ════════════════════════════════════════════════════
       GRAND TOTAL BAR
    ════════════════════════════════════════════════════ */
    .grand-total-bar {
        background: linear-gradient(135deg,#1B2A8A,#2851A3);
        border-radius: 14px; padding: 12px 18px;
        display: flex; align-items: center; justify-content: space-between;
        animation: fadeUp .4s ease both;
        position: relative; overflow: hidden;
    }
    .grand-total-bar::before {
        content: ''; position: absolute; inset: 0; opacity: .08;
        background-image:
            linear-gradient(rgba(255,255,255,.5) 1px,transparent 1px),
            linear-gradient(90deg,rgba(255,255,255,.5) 1px,transparent 1px);
        background-size: 16px 16px;
    }
    .gt-label { font-size: 10px; font-weight: 700; color: rgba(255,255,255,.7); }
    .gt-value { font-size: 20px; font-weight: 900; color: white; }

    /* ════════════════════════════════════════════════════
       FOOTER
    ════════════════════════════════════════════════════ */
    .ld-footer {
        display: flex; align-items: center; justify-content: space-between;
        padding: 10px 2px; font-size: 9px; color: #94a3b8;
        margin-top: 8px; flex-wrap: wrap; gap: 6px;
    }

    /* ════════════════════════════════════════════════════
       RESPONSIVE
    ════════════════════════════════════════════════════ */
    @media(max-width:768px) {
        .tab-nav { gap: 2px; }
        .tab-btn { padding: 5px 12px; font-size: 11px; }
        .kpi-grid { grid-template-columns: 1fr 1fr; }
        .wavg-grid { grid-template-columns: 1fr 1fr; }
        .hd-controls .exp-btn span { display: none; }
    }
    @media(max-width:480px) {
        .ld-grid { grid-template-columns: 1fr; }
    }

    /* ════════════════════════════════════════════════════
       NO DATA STATE
    ════════════════════════════════════════════════════ */
    .no-data {
        text-align: center; padding: 80px 20px; background: white;
        border-radius: 20px; margin-top: 20px; animation: scaleIn .4s ease;
    }
    .no-data-icon {
        width: 64px; height: 64px; border-radius: 20px;
        background: #EFF4FB; display: flex; align-items: center; justify-content: center;
        margin: 0 auto 16px; animation: floatY 3s ease-in-out infinite;
    }

    .export-mode * {
    animation: none !important;
    transition: none !important;
    transform: none !important;
    line-height: 1.2 !important;
    }

    .export-mode .table-scroll {
    max-height: none !important;
    overflow: visible !important;
    }

    .export-mode body {
    -webkit-font-smoothing: antialiased;
    text-rendering: geometricPrecision;
}

    .export-mode .kpi-card {
    display: flex !important;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.export-mode .kpi-label {
    line-height: 1 !important;
    margin-bottom: 4px;
}

.export-mode .kpi-value {
    line-height: 1 !important;
    display: block;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center; /* TAMBAHAN */
    line-height: 1 !important;
    height: 20px; /* FIX HEIGHT */
}

.export-mode .report-title-bar {
    align-items: center !important;
}

.export-mode .report-title-bar > div:last-child {
    flex-wrap: nowrap !important;
    align-items: center !important;
}
</style>
@endpush

@section('content')
<main class="ld-main" id="report-canvas">
@if(!$data['has_data'])
{{-- No data state --}}
<div class="no-data">
    <div class="no-data-icon">
        <svg style="width:30px;height:30px;stroke:#1B2A8A;fill:none;stroke-width:1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
        </svg>
    </div>
    <p style="font-size:15px;font-weight:800;color:#1B2A8A;margin-bottom:6px">Belum ada data loading</p>
    <p style="font-size:13px;color:#94a3b8">
        Upload file Excel di
        <a href="/admin" style="color:#1B2A8A;font-weight:700;text-decoration:none">Admin Panel</a>.
    </p>
</div>

@else

{{-- Report title bar --}}
<div class="report-title-bar">
    <div>
        <p class="report-title" style="display:flex;align-items:center;gap:8px" id="report-heading">
            {{-- ITM Logo --}}
            <span style="display:flex; align-items:center;gap:4px">
                <img src="{{ asset('img/ITM_Logo_1.png') }}" alt="ITM Logo" style="width:66px;height:66px;object-fit:contain;border-radius:12px;margin-bottom:16px;padding:6px;">
            </span>
             SUMMARY LOADING AS {{ strtoupper($data['snapshot']['month_label'] ?? '') }}
        </p>
        <div class="report-subtitle">
            <span class="live-dot"></span>
            LAST UPDATE ON {{ strtoupper($data['snapshot']['upload_date'] ?? '') }}
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
        <div class="snap-badge">
            <svg style="width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Data: {{ $data['snapshot']['upload_date'] }}
        </div>
    </div>
</div>

{{-- ════════════ OVERALL TAB ════════════ --}}
<div class="tab-panel active" id="panel-overall">
    <div class="ld-grid">

        {{-- LEFT: Two tables (BoCT + Mahakam) --}}
        <div class="left-panel">

            {{-- Grand total bar --}}
            <div class="grand-total-bar">
                <div style="position:relative">
                    <p class="gt-label">Total All Load Ports</p>
                    <p class="gt-value">{{ number_format($data['overall']['grand_total']) }} ton</p>
                </div>
                <div style="display:flex;gap:20px;position:relative">
                    @foreach([
                        ['BoCT', $data['overall']['boct_total'], '#60A5FA'],
                        ['Mahakam', $data['overall']['mahakam_total'], '#34D399'],
                    ] as [$label,$val,$color])
                    <div style="text-align:center">
                        <p style="font-size:9px;color:rgba(255,255,255,.6);font-weight:700;text-transform:uppercase;letter-spacing:.05em">{{ $label }}</p>
                        <p style="font-size:16px;font-weight:900;color:{{ $color }}">{{ number_format($val) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- BoCT Table --}}
            <div class="section-divider">BoCT Load Port</div>
            <div class="ld-card" style="animation-delay:.05s">
                <div class="card-hd navy">
                    <h2>
                        <span style="display:inline-flex;align-items:center;gap:6px">
                            <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                            </svg>
                            BoCT Summary
                        </span>
                    </h2>
                    <span class="hd-meta">{{ count($data['overall']['boct_rows']) }} shipments · {{ number_format($data['overall']['boct_total']) }} ton</span>
                </div>
                <div class="table-scroll">
                    <table class="ld-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>End user</th>
                                <th>TS (AR)</th>
                                <th>CV (AR)</th>
                                <th>Total</th>
                                <th>% Shpr</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['overall']['boct_rows'] as $idx => $row)
                            <tr style="animation:rowSlide .4s ease {{ $idx * 0.03 }}s both">
                                <td class="cell-no">{{ $row['no'] }}</td>
                                <td class="cell-user">{{ $row['end_user'] }}</td>
                                <td class="cell-num" style="text-align:left;">{{ $row['ts_ar'] !== null ? number_format($row['ts_ar'], 2) : '—' }}</td>
                                <td class="cell-num" style="text-align:left;">{{ $row['cv_ar'] !== null ? number_format($row['cv_ar']) : '—' }}</td>
                                <td class="cell-num" style="color:#1B2A8A; text-align:left;">{{ number_format($row['total']) }}</td>
                                <td class="cell-num" style="text-align:left;">{{ $row['pct_shipper'] }}%</td>
                                <td>
                                    <span class="status-badge" style="background:{{ $row['status_color'] }}">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align:right;color:#1B2A8A">TOTAL BoCT</td>
                                <td class="cell-num">{{ number_format($data['overall']['boct_total']) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Mahakam Table --}}
            <div class="section-divider">Mahakam Load Port</div>
            <div class="ld-card" style="animation-delay:.1s">
                <div class="card-hd teal">
                    <h2>
                        <span style="display:inline-flex;align-items:center;gap:6px">
                            <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25"/>
                            </svg>
                            Mahakam Summary
                        </span>
                    </h2>
                    <span class="hd-meta">{{ count($data['overall']['mahakam_rows']) }} shipments · {{ number_format($data['overall']['mahakam_total']) }} ton</span>
                </div>
                <div class="table-scroll">
                    <table class="ld-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>L.Port</th>
                                <th>End user</th>
                                <th>TS (AR)</th>
                                <th>CV (AR)</th>
                                <th>Total</th>
                                <th>% Shpr</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['overall']['mahakam_rows'] as $idx => $row)
                            <tr style="animation:rowSlide .4s ease {{ $idx * 0.03 }}s both">
                                <td class="cell-no">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="port-badge {{ $row['load_port'] === 'Muara Berau' ? 'muara' : 'gpk' }}">
                                        {{ $row['load_port'] === 'Muara Berau' ? 'Muar.' : 'GPK' }}
                                    </span>
                                </td>
                                <td class="cell-user">{{ $row['end_user'] }}</td>
                                <td class="cell-num" style="text-align:left;">{{ $row['ts_ar'] !== null ? number_format($row['ts_ar'], 2) : '—' }}</td>
                                <td class="cell-num" style="text-align:left;">{{ $row['cv_ar'] !== null ? number_format($row['cv_ar']) : '—' }}</td>
                                <td class="cell-num" style="color:#059669; text-align:left;">{{ number_format($row['total']) }}</td>
                                <td class="cell-num" style="text-align:left;">{{ $row['pct_shipper'] }}%</td>
                                <td>
                                    <span class="status-badge" style="background:{{ $row['status_color'] }}">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-align:right;color:#059669">TOTAL MAHAKAM</td>
                                <td class="cell-num" style="color:#059669">{{ number_format($data['overall']['mahakam_total']) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- RIGHT: KPI + Charts --}}
        <div class="right-panel">
            {{-- KPI grid --}}
            <div class="kpi-grid">
                @foreach([['Completed','#06B6D4'],['In Progress','#F59E0B'],['Loading','#F97316'],['Plan','#EC4899']] as [$s,$c])
                <div class="kpi-card" style="border-left-color:{{ $c }};animation-delay:{{ $loop->index * 0.08 }}s">
                    <p class="kpi-label" style="color:{{ $c }}">{{ $s }}</p>
                    <p class="kpi-value" style="color:{{ $c }}">{{ number_format($data['overall']['kpi'][$s] ?? 0) }}</p>
                    <p class="kpi-unit">Ton</p>
                </div>
                @endforeach
            </div>

            <div class="chart-card">
                <p class="chart-title">Total Tonnages by Shipper / Company</p>
                <div class="chart-wrap" style="height:150px"><canvas id="chart-ov-company"></canvas></div>
            </div>
            <div class="chart-card">
                <p class="chart-title">Total Number of Shipment</p>
                <div class="chart-wrap" style="height:150px"><canvas id="chart-ov-count"></canvas></div>
            </div>
            <div class="chart-card">
                <p class="chart-title">Total by Load Port</p>
                <div class="chart-wrap" style="height:150px"><canvas id="chart-ov-port"></canvas></div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════ BOCT TAB ════════════ --}}
<div class="tab-panel" id="panel-boct">
    <div class="ld-grid">
        <div class="left-panel">
            <div class="ld-card">
                <div class="card-hd navy">
                    <h2>BoCT Summary Loading</h2>
                    <span class="hd-meta">{{ number_format($data['boct']['total']) }} ton</span>
                </div>
                <div class="table-scroll">
                    <table class="ld-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>L.Port</th>
                                <th>End user</th>
                                <th>ETA</th><th>ETB</th><th>ETD</th>
                                <th>Total</th>
                                <th>Lay</th><th>Can</th>
                                <th>{{ $data['pen_week_label'] }}</th>
                                <th>{{ $data['dem_week_label'] }}</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['boct']['rows'] as $idx => $row)
                            <tr style="animation:rowSlide .4s ease {{ $idx * 0.025 }}s both">
                                <td class="cell-no">{{ $row['no'] }}</td>
                                <td><span class="port-badge boct">BoCT</span></td>
                                <td class="cell-user">{{ $row['end_user'] }}</td>
                                <td style="font-size:10px;color:#64748b">{{ $row['eta'] ?? '—' }}</td>
                                <td style="font-size:10px;color:#64748b">{{ $row['etb'] ?? '—' }}</td>
                                <td style="font-size:10px;color:#64748b">{{ $row['etd'] ?? '—' }}</td>
                                <td class="cell-num" style="color:#1B2A8A">{{ number_format($row['total']) }}</td>
                                <td style="font-size:10px;color:#64748b">{{ $row['lay'] ?? '—' }}</td>
                                <td style="font-size:10px;color:#64748b">{{ $row['can'] ?? '—' }}</td>
                                @php $pv = $row['pen_w']; $dv = $row['dem_w']; @endphp
                                <td class="cell-num {{ $pv === null ? 'cell-neutral' : ($pv >= 0 ? 'cell-positive' : 'cell-negative') }}">
                                    {{ $pv !== null ? number_format($pv, 2) : '—' }}
                                </td>
                                <td class="cell-num {{ $dv === null ? 'cell-neutral' : ($dv >= 0 ? 'cell-positive' : 'cell-negative') }}">
                                    {{ $dv !== null ? number_format($dv, 2) : '—' }}
                                </td>
                                <td>
                                    <span class="status-badge" style="background:{{ $row['status_color'] }}">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" style="text-align:right">TOTAL</td>
                                <td class="cell-num">{{ number_format($data['boct']['total']) }}</td>
                                <td colspan="5"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Weight Avg --}}
                <div class="wavg-section">
                    <div class="wavg-grid">
                        @php $pv = $data['boct']['weight_pen']; $dv = $data['boct']['weight_dem']; @endphp
                        <div class="wavg-card">
                            <p class="wavg-label">Weight Avg {{ $data['pen_week_label'] }}</p>
                            <p class="wavg-val {{ $pv === null ? 'zero' : ($pv >= 0 ? 'pos' : 'neg') }}">
                                {{ $pv !== null ? number_format($pv, 2) : 'N/A' }}
                            </p>
                        </div>
                        <div class="wavg-card">
                            <p class="wavg-label">Weight Avg {{ $data['dem_week_label'] }}</p>
                            <p class="wavg-val {{ $dv === null ? 'zero' : ($dv >= 0 ? 'pos' : 'neg') }}">
                                {{ $dv !== null ? number_format($dv, 2) : 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="right-panel">
            <div class="kpi-grid">
                @foreach([['Completed','#06B6D4'],['Loading','#F97316'],['Plan','#EC4899']] as [$s,$c])
                <div class="kpi-card" style="border-left-color:{{ $c }};animation-delay:{{ $loop->index * 0.08 }}s">
                    <p class="kpi-label" style="color:{{ $c }}">{{ $s }}</p>
                    <p class="kpi-value" style="color:{{ $c }}">{{ number_format($data['boct']['kpi'][$s] ?? 0) }}</p>
                    <p class="kpi-unit">Ton</p>
                </div>
                @endforeach
            </div>
            <div class="chart-card">
                <p class="chart-title">Total Tonnages by Shipper / Company</p>
                <div class="chart-wrap" style="height:150px"><canvas id="chart-boct-company"></canvas></div>
            </div>
            <div class="chart-card">
                <p class="chart-title">Total Number of Shipment</p>
                <div class="chart-wrap" style="height:150px"><canvas id="chart-boct-count"></canvas></div>
            </div>
            <div class="chart-card">
                <p class="chart-title">Total by Product</p>
                <div class="chart-wrap" style="height:180px"><canvas id="chart-boct-product"></canvas></div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════ MAHAKAM TAB ════════════ --}}
<div class="tab-panel" id="panel-mahakam">
    <div class="ld-grid">
        <div class="left-panel">
            <div class="ld-card">
                <div class="card-hd teal">
                    <h2>Mahakam Summary Loading</h2>
                    <span class="hd-meta">{{ number_format($data['mahakam']['total']) }} ton</span>
                </div>
                <div class="table-scroll">
                    <table class="ld-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>L.Port</th>
                                <th>End user</th>
                                <th>ETA</th><th>ETB</th><th>ETD</th>
                                <th>Total</th>
                                <th>Lay</th><th>Can</th>
                                <th>{{ $data['pen_week_label'] }}</th>
                                <th>{{ $data['dem_week_label'] }}</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['mahakam']['rows'] as $idx => $row)
                            <tr style="animation:rowSlide .4s ease {{ $idx * 0.025 }}s both">
                                <td class="cell-no">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="port-badge {{ $row['load_port'] === 'Muara Berau' ? 'muara' : 'gpk' }}">
                                        {{ $row['load_port'] === 'Muara Berau' ? 'Muar.' : 'GPK' }}
                                    </span>
                                </td>
                                <td class="cell-user">{{ $row['end_user'] }}</td>
                                <td style="font-size:10px;color:#64748b">{{ $row['eta'] ?? '—' }}</td>
                                <td style="font-size:10px;color:#64748b">{{ $row['etb'] ?? '—' }}</td>
                                <td style="font-size:10px;color:#64748b">{{ $row['etd'] ?? '—' }}</td>
                                <td class="cell-num" style="color:#059669">{{ number_format($row['total']) }}</td>
                                <td style="font-size:10px;color:#64748b">{{ $row['lay'] ?? '—' }}</td>
                                <td style="font-size:10px;color:#64748b">{{ $row['can'] ?? '—' }}</td>
                                @php $pv = $row['pen_w']; $dv = $row['dem_w']; @endphp
                                <td class="cell-num {{ $pv === null ? 'cell-neutral' : ($pv >= 0 ? 'cell-positive' : 'cell-negative') }}">
                                    {{ $pv !== null ? number_format($pv, 2) : '—' }}
                                </td>
                                <td class="cell-num {{ $dv === null ? 'cell-neutral' : ($dv >= 0 ? 'cell-positive' : 'cell-negative') }}">
                                    {{ $dv !== null ? number_format($dv, 2) : '—' }}
                                </td>
                                <td>
                                    <span class="status-badge" style="background:{{ $row['status_color'] }}">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" style="text-align:right">TOTAL</td>
                                <td class="cell-num">{{ number_format($data['mahakam']['total']) }}</td>
                                <td colspan="5"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="wavg-section">
                    <div class="wavg-grid">
                        @php $pv = $data['mahakam']['weight_pen']; $dv = $data['mahakam']['weight_dem']; @endphp
                        <div class="wavg-card">
                            <p class="wavg-label">Weight Avg {{ $data['pen_week_label'] }}</p>
                            <p class="wavg-val {{ $pv === null ? 'zero' : ($pv >= 0 ? 'pos' : 'neg') }}">
                                {{ $pv !== null ? number_format($pv, 2) : 'N/A' }}
                            </p>
                        </div>
                        <div class="wavg-card">
                            <p class="wavg-label">Weight Avg {{ $data['dem_week_label'] }}</p>
                            <p class="wavg-val {{ $dv === null ? 'zero' : ($dv >= 0 ? 'pos' : 'neg') }}">
                                {{ $dv !== null ? number_format($dv, 2) : 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="right-panel">
            <div class="kpi-grid">
                @foreach([['Completed','#06B6D4'],['In Progress','#F59E0B'],['Loading','#F97316'],['Plan','#EC4899']] as [$s,$c])
                <div class="kpi-card" style="border-left-color:{{ $c }};animation-delay:{{ $loop->index * 0.08 }}s">
                    <p class="kpi-label" style="color:{{ $c }}">{{ $s }}</p>
                    <p class="kpi-value" style="color:{{ $c }}">{{ number_format($data['mahakam']['kpi'][$s] ?? 0) }}</p>
                    <p class="kpi-unit">Ton</p>
                </div>
                @endforeach
            </div>
            <div class="chart-card">
                <p class="chart-title">Total Tonnages by Shipper / Company</p>
                <div class="chart-wrap" style="height:150px"><canvas id="chart-mah-company"></canvas></div>
            </div>
            <div class="chart-card">
                <p class="chart-title">Total Number of Shipment</p>
                <div class="chart-wrap" style="height:150px"><canvas id="chart-mah-count"></canvas></div>
            </div>
            <div class="chart-card">
                <p class="chart-title">Total by Load Port</p>
                <div class="chart-wrap" style="height:150px"><canvas id="chart-mah-port"></canvas></div>
            </div>
        </div>
    </div>
</div>

{{-- Footer --}}
<div class="ld-footer">
    <span><strong style="color:#64748b">ITM Summary Loading Dashboard</strong> · Confidential · {{ $data['snapshot']['month_label'] ?? '' }}</span>
    <span>Generated {{ now()->format('d M Y H:i') }}</span>
</div>

@endif
</main>

@push('scripts')
<script>
const DATA = @json($data);
let currentTab = 'overall';

window.addEventListener('DOMContentLoaded', () => {

/* ── Tab switching ──────────────────────── */
const HEADINGS = {
    overall: 'ITM SUMMARY LOADING AS {{ strtoupper($data["snapshot"]["month_label"] ?? "") }}',
    boct:    'BOCT SUMMARY LOADING AS {{ strtoupper($data["snapshot"]["month_label"] ?? "") }}',
    mahakam: 'MAHAKAM SUMMARY LOADING AS {{ strtoupper($data["snapshot"]["month_label"] ?? "") }}',
};

function switchTab(tab, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('panel-' + tab)?.classList.add('active');
    btn.classList.add('active');
    const h = document.getElementById('report-heading');
    if (h) h.textContent = HEADINGS[tab] || HEADINGS.overall;
    currentTab = tab;
}

/* ── Chart defaults ─────────────────────── */
Chart.defaults.font.family = 'Inter, sans-serif';
Chart.defaults.animation   = { duration: 700, easing: 'easeOutQuart' };

const tip = {
    backgroundColor: '#0F172A', padding: 10, cornerRadius: 8,
    titleFont: { size: 11, weight: '700' },
    bodyFont:  { size: 11 },
};

/* ── Horizontal bar (Tonnage by Company) ── */
function hBar(id, d) {
    const ctx = document.getElementById(id);
    if (!ctx || !d?.datasets?.length) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: d.labels,
            datasets: d.datasets.map(ds => ({
                label: ds.label,
                data: ds.data,
                backgroundColor: ds.color,
                borderRadius: 6,
                borderSkipped: false,
            }))
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            animation: { delay: ctx => ctx.dataIndex * 60 },
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        font: { size: 9 },
                        color: '#64748b',
                        boxWidth: 10,
                        padding: 8
                    }
                },
                tooltip: {
                    ...tip,
                    callbacks: {
                        label: c =>
                            ` ${c.dataset.label}: ${Math.round(c.raw).toLocaleString('id-ID')} ton`
                    }
                }
            },
            scales: {
                x: {
                    stacked: true,
                    grid: { color: 'rgba(148,163,184,.1)' },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 9 },
                        callback: v =>
                            v >= 1000000
                                ? (v / 1000000).toFixed(1) + 'M'
                                : v >= 1000
                                ? Math.round(v / 1000) + 'K'
                                : v
                    }
                },
                y: {
                    stacked: true,
                    grid: { display: false },
                    ticks: {
                        color: '#374151',
                        font: { size: 10, weight: '600' }
                    }
                }
            }
        },

        // ✅ PLUGIN LABEL DI DALAM BAR
        plugins: [{
            id: 'inlineValue',
            afterDatasetsDraw(chart) {
                const { ctx } = chart;

                function formatShortNumber(val) {
                    if (val >= 1000000) {
                        return (val / 1000000).toFixed(1).replace('.0','') + 'M';
                    }
                    if (val >= 1000) {
                        return (val / 1000).toFixed(0) + 'K';
                    }
                    return val.toString();
                }

                chart.data.datasets.forEach((dataset, i) => {
                    const meta = chart.getDatasetMeta(i);

                    meta.data.forEach((bar, index) => {
                        const value = dataset.data[index];
                        if (!value) return;

                        const width = bar.width;

                        // hide kalau bar kecil
                        if (width < 25) return;

                        ctx.save();
                        ctx.font = '600 9px Inter';
                        ctx.fillStyle = '#ffffff';
                        ctx.textAlign = 'right';
                        ctx.textBaseline = 'middle';

                        const text = formatShortNumber(value);

                        ctx.fillText(text, bar.x - 6, bar.y);

                        ctx.restore();
                    });
                });
            }
        }]
    });
}

/* ── Vertical bar (Shipment count) ──────── */
function vBar(id, d) {
    const ctx = document.getElementById(id);
    if (!ctx || !d?.datasets?.length) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: d.labels,
            datasets: d.datasets.map(ds => ({
                label: ds.label,
                data: ds.data,
                backgroundColor: ds.color,
                borderRadius: 6
            }))
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: { top: 20 }
            },
            plugins: {
                legend: { position: 'bottom' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grace: '20%'
                }
            }
        },
        plugins: [{
            id: 'topLabel',
            afterDatasetsDraw(chart) {
                const { ctx } = chart;

                chart.data.datasets.forEach((dataset, i) => {
                    const meta = chart.getDatasetMeta(i);

                    meta.data.forEach((bar, index) => {
                        const val = dataset.data[index];
                        if (!val) return;

                        ctx.save();
                        ctx.font = '700 11px Inter';
                        ctx.textAlign = 'center';
                        ctx.fillStyle = '#111827';
                        ctx.fillText(val, bar.x, bar.y - 5);
                        ctx.restore();
                    });
                });
            }
        }]
    });
}

/* ── Doughnut / Pie ─────────────────────── */
function pie(id, d) {
    const ctx = document.getElementById(id);
    if (!ctx || !d?.data?.length) return;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: d.labels,
            datasets: [{ data:d.data, backgroundColor:d.colors, borderWidth:2, borderColor:'#fff', hoverOffset:6 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '45%',
            plugins: {
                legend: {
                    display: true, position: 'right',
                    labels: {
                        font:{size:9}, color:'#64748b', boxWidth:10, padding:5,
                        generateLabels: chart => {
                            const ds = chart.data.datasets[0];
                            const total = ds.data.reduce((a,b)=>a+b, 0);
                            return chart.data.labels.map((l,i) => ({
                                text: `${l} ${(ds.data[i]/1000).toFixed(0)}K`,
                                fillStyle: ds.backgroundColor[i], index: i,
                            }));
                        }
                    }
                },
                tooltip: { ...tip, callbacks: {
                    label: c => ` ${c.label}: ${Math.round(c.raw).toLocaleString('id-ID')} ton`
                }}
            },
            animation: { animateRotate: true, delay: i => i.index * 50 }
        }
    });
}

/* ── Build all charts ───────────────────── */

if (DATA.has_data) {
    hBar('chart-ov-company',   DATA.overall.chart_company);
    vBar('chart-ov-count',     DATA.overall.chart_count);
    pie ('chart-ov-port',      DATA.overall.chart_port);

    hBar('chart-boct-company', DATA.boct.chart_company);
    vBar('chart-boct-count',   DATA.boct.chart_count);
    pie ('chart-boct-product', DATA.boct.chart_product);

    hBar('chart-mah-company',  DATA.mahakam.chart_company);
    vBar('chart-mah-count',    DATA.mahakam.chart_count);
    pie ('chart-mah-port',     DATA.mahakam.chart_port);
}});


/* ── Export ─────────────────────────────── */
    async function exportReport(format) {
        document.body.classList.add('export-mode');

        await document.fonts.ready;
        await new Promise(r => setTimeout(r, 300));

        const el = document.getElementById('report-canvas');

        const canvas = await html2canvas(el, {
            scale: 3,
            useCORS: true,
            allowTaint: false,
            backgroundColor: '#ffffff',
            scrollY: -window.scrollY,
        });

        const a = document.createElement('a');
        a.href = canvas.toDataURL(
            format === 'jpg' ? 'image/jpeg' : 'image/png',
            1
        );

        a.download = `ITM_Loading_${currentTab.toUpperCase()}.${format}`;
        a.click();

        document.body.classList.remove('export-mode');
    };
</script>
@endpush
@endsection