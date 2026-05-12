<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>POA Dashboard — ITM</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('img/ITM_Logo_3.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* ── Animations ─────────────────────────────────────────── */
        @keyframes fadeUp  { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
        @keyframes fadeIn  { from{opacity:0} to{opacity:1} }
        @keyframes pulse   { 0%,100%{opacity:1} 50%{opacity:.4} }
        @keyframes shimmer {
            0%  { background-position: -200% 0 }
            100%{ background-position:  200% 0 }
        }

        /* ── Base ───────────────────────────────────────────────── */
        body { font-family:'Inter',sans-serif; background:#EFF4FB; min-height:100vh; }

        /* ── Header ─────────────────────────────────────────────── */
        .poa-header {
            background: linear-gradient(135deg, #1B2A8A 0%, #2851A3 60%, #1D9E75 100%);
            padding: 0 24px;
            position: sticky; top: 0; z-index: 50;
        }
        .poa-header-inner {
            max-width: 1400px; margin: 0 auto;
            height: 60px; display: flex; align-items: center; justify-content: space-between;
        }
        .poa-title-block { display: flex; align-items: center; gap: 10px; }
        .poa-logo-box {
            width: 36px; height: 36px; background: rgba(255,255,255,.2);
            border-radius: 10px; display: flex; align-items: center; justify-content: center;
        }
        .poa-logo-box svg { width: 20px; height: 20px; stroke: white; fill: none; stroke-width: 1.5; }
        .poa-main-title { font-size: 14px; font-weight: 900; color: white; }
        .poa-sub-title  { font-size: 9px; color: rgba(255,255,255,.6); margin-top: 1px; }

        /* ── Controls ───────────────────────────────────────────── */
        .poa-controls {
            display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
        }
        .poa-date-select {
            padding: 5px 10px; font-size: 11px; font-weight: 600;
            background: rgba(255,255,255,.15); color: white;
            border: 1px solid rgba(255,255,255,.25); border-radius: 9px;
            cursor: pointer; outline: none; font-family: 'Inter',sans-serif;
            transition: all .15s;
        }
        .poa-date-select option { background: #1B2A8A; color: white; }
        .poa-date-select:hover { background: rgba(255,255,255,.25); }
        .poa-export-btn {
            display: flex; align-items: center; gap: 5px;
            padding: 6px 12px; font-size: 11px; font-weight: 700;
            border-radius: 9px; cursor: pointer; transition: all .15s;
            font-family: 'Inter',sans-serif; border: none;
        }
        .poa-export-btn svg { width: 13px; height: 13px; stroke: currentColor; fill: none; stroke-width: 2; }
        .btn-png { background: rgba(255,255,255,.15); color: white; border: 1px solid rgba(255,255,255,.25); }
        .btn-png:hover { background: rgba(255,255,255,.25); }
        .btn-jpg { background: white; color: #1B2A8A; }
        .btn-jpg:hover { background: #EFF4FB; transform: translateY(-1px); }

        /* ── Page content ────────────────────────────────────────── */
        .poa-content { max-width: 1400px; margin: 0 auto; padding: 20px 24px; }

        /* ── Report title banner ─────────────────────────────────── */
        .poa-report-banner {
            background: white; border-radius: 18px;
            padding: 16px 24px; margin-bottom: 16px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 2px 12px rgba(27,42,138,.08);
            animation: fadeUp .5s ease both;
        }
        .poa-report-title {
            font-size: 22px; font-weight: 900; color: #1B2A8A;
        }
        .poa-report-title span {
            background: linear-gradient(135deg, #1B2A8A, #1D9E75);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .poa-snapshot-badge {
            display: flex; align-items: center; gap: 6px;
            background: #EFF4FB; border-radius: 10px;
            padding: 6px 12px; font-size: 11px;
        }
        .poa-snapshot-badge .dot {
            width: 6px; height: 6px; background: #1D9E75;
            border-radius: 50%; animation: pulse 2s infinite;
        }

        /* ── Legend ──────────────────────────────────────────────── */
        .poa-legend {
            display: flex; align-items: center; gap: 16px;
            padding: 10px 16px; background: rgba(255,255,255,.7);
            border-radius: 10px; backdrop-filter: blur(8px);
        }
        .legend-item { display: flex; align-items: center; gap: 5px; }
        .legend-dot  { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
        .legend-label{ font-size: 10px; font-weight: 600; color: #475569; }
        .legend-note { font-size: 9px; color: #94a3b8; font-style: italic; }

        /* ── Chart cards grid ────────────────────────────────────── */
        .poa-grid {
            display: flex; flex-direction: column; gap: 14px;
        }
        .poa-chart-card {
            background: white; border-radius: 18px;
            padding: 16px 20px 14px;
            box-shadow: 0 2px 14px rgba(27,42,138,.09);
            border: 1.5px solid #f1f5f9;
            animation: fadeUp .5s ease both;
            transition: box-shadow .2s, border-color .2s;
        }
        .poa-chart-card:hover {
            box-shadow: 0 6px 28px rgba(27,42,138,.15);
            border-color: #BFDBFE;
        }

        /* ── Company logo/header ─────────────────────────────────── */
        .poa-card-header {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 10px;
            
        }           
        .company-logo {
            margin:auto;
            height: 58px; width: auto; max-width: 120px; object-fit: contain;
        }
        .company-logo-fallback {
            display: flex; align-items: center; gap: 6px;
            background: #1B2A8A; color: white; padding: 5px 12px;
            border-radius: 8px; font-size: 12px; font-weight: 800;
        }
        .company-logo-fallback svg { stroke: white; }

        /* ── Chart canvas ────────────────────────────────────────── */
        .poa-chart-wrap { position: relative; height: 220px; }
        .poa-canvas { width: 100% !important; height: 100% !important; }

        /* ── No data state ───────────────────────────────────────── */
        .poa-no-data {
            text-align: center; padding: 60px 24px;
            background: white; border-radius: 18px;
        }

        /* ── Admin info strip ────────────────────────────────────── */
        .admin-strip {
            background: #1B2A8A; color: white;
            padding: 8px 24px; font-size: 11px;
            display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
        }
        .admin-strip a {
            color: #93C5FD; text-decoration: none; font-weight: 600;
        }
        .admin-strip a:hover { color: white; }

        /* ── Footer ──────────────────────────────────────────────── */
        .poa-footer {
            text-align: center; padding: 16px 24px;
            font-size: 10px; color: #94a3b8;
        }




    </style>
</head>
<body>

{{-- HEADER --}}
<header class="poa-header">
    <div class="poa-header-inner">
        <div class="poa-title-block">
            <a href="{{ route('home') }}" style="text-decoration:none;display:flex;align-items:center;gap:8px">
                <span style="font-size:11px;font-weight:600;color:rgba(255,255,255,.7);
                             display:flex;align-items:center;gap:4px">
                    <svg style="width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                    </svg>
                    Back to Homepage
                </span>
            </a>
            <div style="width:1px;height:20px;background:rgba(255,255,255,.2)"></div>
            <div class="poa-logo-box">
                <svg viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                </svg>
            </div>
            <div>
                <div class="poa-main-title">POA Dashboard</div>
                <div class="poa-sub-title">Previous Outlook vs Outlook vs Actual</div>
            </div>
        </div>

        <div class="poa-controls">
            {{-- Date picker --}}
            @if(count($availableDates) > 0)
            <select class="poa-date-select"
                    onchange="window.location.href='/poa?date='+this.value.split('|')[0]+'&year='+this.value.split('|')[1]">
                @foreach($availableDates as $d)
                <option value="{{ $d['date'] }}|{{ $d['data_year'] }}"
                        {{ $d['date'] === $selectedDate ? 'selected' : '' }}>
                    {{ $d['label'] }}
                </option>
                @endforeach
            </select>
            @endif

            <button class="poa-export-btn btn-png" onclick="exportPOA('png')">
                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                PNG
            </button>
            <button class="poa-export-btn btn-jpg" onclick="exportPOA('jpg')">
                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                JPG
            </button>
        </div>
    </div>
</header>

{{-- MAIN --}}
<main class="poa-content" id="poa-canvas">

    @if($data['has_data'])

    {{-- Report title banner --}}
    <div class="poa-report-banner">
        <div>
            <h1 class="poa-report-title">
                Previous Outlook vs Outlook vs
                <span>Actual {{ $data['year'] }}</span>
            </h1>
            <p style="font-size:11px;color:#94a3b8;margin-top:3px">
                Data: {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}
            </p>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            {{-- Legend --}}
            <div class="poa-legend">
                <div class="legend-item">
                    <div class="legend-dot" style="background:#B5D4F4"></div>
                    <span class="legend-label">Previous</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot" style="background:#1B2A8A"></div>
                    <span class="legend-label">Outlook</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot" style="background:#E8540A"></div>
                    <span class="legend-label">Actual</span>
                </div>
                <span class="legend-note">(*) Number Still Prediction</span>
            </div>
            {{-- Snapshot info --}}
            <div class="poa-snapshot-badge">
                <span class="dot"></span>
                <span style="color:#475569;font-weight:600;font-size:10px">
                    Snapshot: {{ $data['snapshot']['upload_date'] }}
                </span>
            </div>
        </div>
    </div>

    {{-- Chart cards --}}
    <div class="poa-grid" id="poa-charts">
        @foreach($data['companies'] as $i => $company)
        <x-poa-chart
            :company="$company['company']"
            :chartData="$company['monthly_data']"
            :yMax="$company['y_max']"
            :index="$i"
        />
        @endforeach
    </div>

    {{-- Report footer --}}
    <div style="display:flex;align-items:center;justify-content:space-between;
                padding:10px 4px;margin-top:8px;font-size:9px;color:#94a3b8">
        <span>
            <strong style="color:#64748b">ITM POA Dashboard</strong>
            · Confidential · {{ $data['year'] }}
        </span>
        <span>Generated {{ now()->format('d M Y H:i') }}</span>
    </div>

    @else
    <div class="poa-no-data">
        <svg style="width:48px;height:48px;stroke:#94a3b8;fill:none;stroke-width:1.5;margin:0 auto 16px;display:block" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
        </svg>
        <p style="font-size:14px;font-weight:700;color:#475569;margin:0 0 6px">Belum ada data POA</p>
        <p style="font-size:12px;color:#94a3b8">
            Please upload the Excel file at
            <a href="/admin" style="color:#1B2A8A;font-weight:600">Admin Panel</a>
            first.
        </p>
    </div>
    @endif

</main>

<div class="poa-footer">
    &copy; {{ date('Y') }} PT Indo Tambangraya Megah Tbk — CBIC POA Dashboard System
</div>


<script>
/* ── Warna POA ─────────────────────────────────────────────── */
const POA_COLORS = {
    previous: { bar: '#B5D4F4', border: '#5B8DD9' },
    outlook:  { bar: '#1B2A8A', border: '#0D1B5E' },
    actual:   { bar: '#E8540A', border: '#C04000' },
};

const MONTH_LABELS = [
    'January','February','March','April','May','June',
    'July','August','September','October','November','December'
];

window.addEventListener('DOMContentLoaded', () => {

/* ── Inisialisasi semua chart ──────────────────────────────── */
document.querySelectorAll('.poa-canvas').forEach(canvas => {
    const chartData = JSON.parse(canvas.dataset.chart);
    const yMax      = parseInt(canvas.dataset.ymax, 10);
    const company   = canvas.dataset.company;

    buildChart(canvas, chartData, yMax, company);
});

function buildChart(canvas, data, yMax, company) {
    const labels   = data.map(m => m.month_name);
    const previous = data.map(m => m.previous || 0);
    const outlook  = data.map(m => m.outlook  || 0);
    const actual   = data.map(m => m.actual   || 0);

    // Tandai bar actual yang masih prediksi dengan opacity berbeda
    const actualColors = data.map(m =>
        m.is_prediction
            ? 'rgba(232,84,10,0.3)'
            : POA_COLORS.actual.bar
    );

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Previous',
                    data: previous,
                    backgroundColor: POA_COLORS.previous.bar,
                    borderColor:     POA_COLORS.previous.border,
                    borderWidth: 0,
                    borderRadius: 4,
                    borderSkipped: false,
                },
                {
                    label: 'Outlook',
                    data: outlook,
                    backgroundColor: POA_COLORS.outlook.bar,
                    borderColor:     POA_COLORS.outlook.border,
                    borderWidth: 0,
                    borderRadius: 4,
                    borderSkipped: false,
                },
                {
                    label: 'Actual',
                    data: actual,
                    backgroundColor: actualColors,
                    borderColor:     POA_COLORS.actual.border,
                    borderWidth: 0,
                    borderRadius: 4,
                    borderSkipped: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 800,
                easing: 'easeOutQuart',
                delay: (ctx) => ctx.dataIndex * 30,
            },
            plugins: {
                legend: { display: false },
                tooltip: {
    callbacks: {
        label: ctx => {
            const m = data[ctx.dataIndex];

            let value = ctx.parsed.y.toLocaleString('en-US');

            // Tambahkan * jika provisional
            if (ctx.dataset.label === 'Actual' && m.is_provisional) {
                value = '*' + value;
            }

            return `${ctx.dataset.label}: ${value}`;
        }
    }
},
                // Plugin custom: tampilkan angka di atas bar
                datalabels: false,
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { family: 'Inter', size: 9 },
                        color: '#94a3b8',
                        maxRotation: 0,
                    },
                },
                y: {
                    min: 0,
                    max: yMax,
                    grid: { color: 'rgba(148,163,184,0.1)' },
                    ticks: {
                        font: { family: 'Inter', size: 9 },
                        color: '#94a3b8',
                        stepSize: Math.ceil(yMax / 4 / 100) * 100,
                    },
                },
            },
        },
        plugins: [{
    id: 'topLabels',
    afterDatasetsDraw(chart) {
        const { ctx } = chart;

        chart.data.datasets.forEach((dataset, di) => {
            const meta = chart.getDatasetMeta(di);

            meta.data.forEach((bar, i) => {
                const val = dataset.data[i];
                if (val <= 0) return;

                // ✅ ambil data bulan
                const m = data[i];

                // ✅ hanya untuk dataset "Actual"
                const isActual = dataset.label === 'Actual';

                // ✅ cek provisional
                const isProvisional = isActual && m?.is_provisional;

                ctx.save();
                ctx.font = '500 9px Inter, sans-serif';
                ctx.fillStyle = isProvisional
                    ? 'rgba(255, 48, 48, 0.8)'
                    : '#475569';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'bottom';

                // ✅ tambahkan * di depan
                const label = isProvisional
                    ? '*' + Math.round(val)
                    : Math.round(val);

                ctx.fillText(label, bar.x, bar.y - 2);
                ctx.restore();
            });
        });
    }
}],
    });
}
});

/* ── Export ────────────────────────────────────────────────── */
async function exportPOA(format) {
    const el     = document.getElementById('poa-canvas');
    const date   = '{{ $selectedDate }}';
    const year   = '{{ $data["year"] ?? now()->year }}';
    const fname  = `ITM_POA_${year}_${date.replace(/-/g,'')}.${format}`;

    // Tampilkan loading indicator
    const btn = document.querySelector(format === 'png' ? '.btn-png' : '.btn-jpg');
    const orig = btn.innerHTML;
    btn.innerHTML = '...';
    btn.disabled = true;

    try {
        const canvas = await html2canvas(el, {
            scale: 2,
            useCORS: true,
            logging: false,
            backgroundColor: format === 'jpg' ? '#FFFFFF' : '#EFF4FB',
            allowTaint: true,
        });

        const a  = document.createElement('a');
        a.href   = canvas.toDataURL(format === 'jpg' ? 'image/jpeg' : 'image/png', 0.92);
        a.download = fname;
        a.click();
    } finally {
        btn.innerHTML = orig;
        btn.disabled  = false;
    }
}
</script>
</body>
</html>