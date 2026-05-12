@extends ('layouts.app')
@section('title','Previous Outlook, Outlook, Actual Report')
@section('page-title', 'POA Dashboard')

@push('head')

<style>
    /* ── Animations ─────────────────────────────────────────── */
    @keyframes fadeUp  { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
    @keyframes fadeIn  { from{opacity:0} to{opacity:1} }
    @keyframes pulse   { 0%,100%{opacity:1} 50%{opacity:.4} }

    /* ── Base ───────────────────────────────────────────────── */
    .poa-content { font-family: 'Inter', sans-serif; }

    /* ── Report title banner ─────────────────────────────────── */
    .poa-report-banner {
        background: white; border-radius: 18px;
        padding: 16px 24px; margin-bottom: 16px;
        display: flex; align-items: center; justify-content: space-between;
        box-shadow: 0 2px 12px rgba(27,42,138,.08);
        animation: fadeUp .5s ease both;
        flex-wrap: wrap; gap: 16px;
    }
    .poa-report-title {
        font-size: 22px; font-weight: 900; color: #1B2A8A; margin: 0;
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
        flex-wrap: wrap;
    }
    .legend-item { display: flex; align-items: center; gap: 5px; }
    .legend-dot  { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
    .legend-label{ font-size: 10px; font-weight: 600; color: #475569; }
    .legend-note { font-size: 9px; color: #94a3b8; font-style: italic; }

    /* ── Chart cards grid ────────────────────────────────────── */
    .poa-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        gap: 14px; margin-bottom: 20px;
    }
    @media (max-width: 1024px) {
        .poa-grid { grid-template-columns: 1fr; }
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
        height: 28px; width: auto; max-width: 120px; object-fit: contain;
    }
    .company-logo-fallback {
        display: flex; align-items: center; gap: 6px;
        background: #1B2A8A; color: white; padding: 5px 12px;
        border-radius: 8px; font-size: 12px; font-weight: 800;
    }
    .company-logo-fallback svg { stroke: white; }

    /* ── Chart canvas ────────────────────────────────────────── */
    .poa-chart-wrap { position: relative; height: 280px; margin: 12px 0; }
    .poa-canvas { width: 100% !important; height: 100% !important; }

    /* ── No data state ───────────────────────────────────────── */
    .poa-no-data {
        text-align: center; padding: 60px 24px;
        background: white; border-radius: 18px;
    }
</style>
@endpush

@section('content')
<div style="padding: 16px 20px; max-width: 1400px; margin: 0 auto;">

    {{-- Admin info strip --}}
    @if($snapshotInfo)
    <div style="background:#EFF4FB;border:1px solid #BFDBFE;border-radius:14px;
                padding:10px 16px;margin-bottom:14px;display:flex;align-items:center;
                justify-content:space-between;flex-wrap:wrap;gap:10px">
        <div style="font-size:11px;color:#1B2A8A;font-weight:600;display:flex;align-items:center;gap:8px">
            <svg style="width:14px;height:14px;stroke:#1B2A8A;fill:none;stroke-width:2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
            </svg>
            Display data:
            <strong>{{ $snapshotInfo->upload_date->format('d M Y') }}</strong>
            ·
            Uploaded by: <strong>{{ $snapshotInfo->uploader?->name ?? '-' }}</strong>
            · {{ $snapshotInfo->total_rows }}  data row
        </div>
        <a href="/admin/poa-imports"
           style="font-size:11px;font-weight:700;color:#1B2A8A;text-decoration:none;
                  background:white;padding:5px 12px;border-radius:8px;border:1px solid #BFDBFE">
            Manage Snapshots ↗
        </a>
    </div>
    @endif

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
                Data : {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}
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
            :snapshotId="$snapshotInfo?->id"
            :year="$data['year']"
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

</div>

<script>
const POA_COLORS = {
    previous: { bar: '#B5D4F4', border: '#93C5FD' },
    outlook:  { bar: '#1B2A8A', border: '#1B2A8A' },
    actual:   { bar: '#E8540A', border: '#C7410F' }
};

const MONTHS = [
    'January','February','March','April',
    'May','June','July','August',
    'September','October','November','December'
];

const canEdit = {{ auth()->user()?->isSuperAdmin() || auth()->user()?->isOperator() ? 'true' : 'false' }};

/* ── Inisialisasi semua chart ──────────────────────────────── */
window.addEventListener('DOMContentLoaded', () => {
document.querySelectorAll('.poa-canvas').forEach(canvas => {
    const chartData = JSON.parse(canvas.dataset.chart);
    const yMax      = parseInt(canvas.dataset.ymax, 10);
    const company   = canvas.dataset.company;

    buildChart(canvas, chartData, yMax, company);
});
});

function buildChart(canvas, data, yMax, company) {
    const labels   = data.map(m => m.month_name);
    const previous = data.map(m => m.previous || 0);
    const outlook  = data.map(m => m.outlook  || 0);
    const actual   = data.map(m => m.actual   || 0);

    // Tambahkan (*) pada label bulan jika is_provisional
    const labelsWithAsterisk = data.map((m, idx) => {
        const label = m.month_name;
        return m.is_provisional ? label + ' *' : label;
    });

    

    // Tandai bar actual yang masih prediksi dengan opacity berbeda
    const actualColors = data.map(m =>
        m.is_prediction || m.is_provisional
            ? 'rgba(232,84,10,0.3)'
            : POA_COLORS.actual.bar
    );

    const chart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: labelsWithAsterisk,
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
            maintainAspectRatio: true,
            indexAxis: undefined,
            scales: {
                y: {
                    beginAtZero: true,
                    max: yMax,
                    ticks: {
                        stepSize: yMax / 5,
                        callback: v => v.toLocaleString('en-US'),
                        font: { size: 10 },
                    },
                    grid: { color: '#e2e8f0', drawBorder: false },
                },
                x: {
                    ticks: { font: { size: 9 } },
                    grid: { display: false, drawBorder: false },
                },
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, padding: 16, font: { size: 11 } },
                },
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
            },
        },
    });

    const starPlugin = {
    id: 'starPlugin',
    afterDatasetsDraw(chart) {
        const { ctx } = chart;

        chart.data.datasets.forEach((dataset, i) => {
            if (dataset.label !== 'Actual') return;

            const meta = chart.getDatasetMeta(i);

            meta.data.forEach((bar, index) => {
                const m = chart.data._customData[index];
                if (!m?.is_provisional) return;

                const pos = bar.tooltipPosition();

                ctx.save();
                ctx.fillStyle = '#E8540A';
                ctx.font = 'bold 14px Inter';
                ctx.textAlign = 'center';
                ctx.fillText('*', pos.x, pos.y - 8);
                ctx.restore();
            });
        });
    }
};

    // Click handler untuk toggle provisional (admin only)
    if (canEdit) {
        canvas.addEventListener('click', (e) => {
            const points = chart.getElementsAtEventForMode(e, 'nearest', {intersect: true}, true);
            if (points.length === 0) return;

            const point = points[0];
            const monthIndex = point.index;
            const monthData = data[monthIndex];

            const snapshotId = canvas.getAttribute('data-snapshot-id');
            const year = canvas.getAttribute('data-year');

            toggleProvisional(snapshotId, company, monthData.month_num, year, !monthData.is_provisional);
        });
    }
}

function toggleProvisional(snapshotId, company, month, year, newValue) {
    fetch('/api/poa/update-provisional', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            snapshot_id: snapshotId,
            company: company,
            month: month,
            year: year,
            is_provisional: newValue,
        }),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Reload to refresh charts
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to update'));
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Failed to update provisional flag');
    });
}
</script>
@endsection