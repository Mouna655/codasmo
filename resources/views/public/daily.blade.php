<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ITM Dashboard — @yield('title', 'Dashboard')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('img/ITM_Logo_3.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="h-full bg-itm-bg font-sans antialiased" x-data="{ sidebarOpen: true, sidebarMobile: false }">

    <div class="px-4 py-4 max-w-[1600px] mx-auto">

    {{-- Controls row --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 no-print">
        <div>
           <button onclick="window.location.href='{{ route('home') }}'" class="btn-outline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Homepage
            </button>
            <h1 class="text-lg sm:text-xl font-black text-itm-navy tracking-tight">ITM PRODUCTION REPORT</h1>
            <p class="text-xs text-slate-400">Daily Production Dashboard</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <input type="date" id="date-picker" value="{{ $date->format('Y-m-d') }}"
                   class="px-3 py-2 text-xs font-semibold text-slate-700 bg-white
                          border border-slate-200 rounded-xl focus:outline-none
                          focus:ring-2 focus:ring-itm-blue/40 cursor-pointer"
                   onchange="window.location.href='/daily?date='+this.value">
         
            <button onclick="exportDash('png')" class="btn-outline">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                PNG
            </button>
            <button onclick="exportDash('jpg')" class="btn-primary">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                JPG
            </button>
         
        </div>
    </div>

    {{-- ── EXPORTABLE CANVAS ──────────────────────────────────────── --}}
    <div id="report-canvas" class="space-y-4 bg-itm-bg rounded-2xl p-2">

        {{-- Date & last update --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-2 px-1">
            <div class="flex items-center gap-2">
                <div class="w-1 h-8 bg-itm-navy rounded-full"></div>
                <span class="text-sm font-bold text-itm-navy">Production Report</span>
            </div>
            <div class="text-left sm:text-right">
                <p class="text-xl sm:text-2xl font-black text-itm-teal">{{ $date->translatedFormat('j F Y') }}</p>
                <p class="text-[10px] text-slate-400">
                    Last update: <span id="last-update">{{ $dashboard['last_input'] }}</span>
                </p>
            </div>
        </div>

        {{-- ── TABEL PRODUKSI ─────────────────────────────────────── --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

            {{-- Daily --}}
            <div class="bg-white rounded-2xl shadow-itm overflow-hidden">
                <div class="px-4 py-3 bg-itm-navy">
                    <h2 class="text-xs font-bold text-white tracking-widest uppercase">Daily Production Report</h2>
                </div>
                <div class="divide-y divide-slate-100 overflow-x-auto">
                    @foreach($dashboard['sites'] as $s)
                    <div class="flex items-stretch min-h-[58px]">
                        <div class="w-[60px] sm:w-[72px] flex-shrink-0 flex items-center justify-center
                                    bg-itm-navy m-2 mr-0 rounded-l-xl">
                            <span class="text-white font-black text-xs sm:text-sm tracking-wider">{{ $s['code'] }}</span>
                        </div>
                        <div class="flex-1 grid grid-cols-2 divide-x divide-slate-100">
                            <div class="px-4 py-2.5 flex flex-col justify-center">
                                <p class="text-[9px] font-semibold text-slate-400 uppercase tracking-wide">Coal Winning</p>
                                <p class="text-xl font-black text-itm-navy mt-0.5" id="dcw-{{ $s['site_id'] }}">
                                    {{ number_format($s['daily']['coal_winning']) }}
                                </p>
                            </div>
                            <div class="px-4 py-2.5 flex flex-col justify-center">
                                <p class="text-[9px] font-semibold text-slate-400 uppercase tracking-wide">FC Production</p>
                                <p class="text-xl font-black text-itm-navy mt-0.5" id="dfc-{{ $s['site_id'] }}">
                                    {{ number_format($s['daily']['fc_production']) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    {{-- ITM total --}}
                    <div class="flex items-stretch min-h-[58px] bg-emerald-50">
                        <div class="w-[72px] flex-shrink-0 flex items-center justify-center bg-itm-teal m-2 mr-0 rounded-l-xl">
                            <span class="text-white font-black text-sm tracking-wider">ITM</span>
                        </div>
                        <div class="flex-1 grid grid-cols-2 divide-x divide-emerald-100">
                            <div class="px-4 py-2.5 flex flex-col justify-center">
                                <p class="text-[9px] font-semibold text-itm-teal uppercase tracking-wide">Coal Winning</p>
                                <p class="text-xl font-black text-itm-teal mt-0.5" id="tot-dcw">
                                    {{ number_format($dashboard['totals']['daily']['coal_winning']) }}
                                </p>
                            </div>
                            <div class="px-4 py-3 flex flex-col justify-center">
                                <p class="text-[9px] font-semibold text-itm-teal uppercase tracking-wide">FC Production</p>
                                <p class="text-xl font-black text-itm-teal mt-0.5" id="tot-dfc">
                                    {{ number_format($dashboard['totals']['daily']['fc_production']) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- MTD --}}
            <div class="bg-white rounded-2xl shadow-itm overflow-hidden">
                <div class="px-4 py-3 bg-itm-blue">
                    <h2 class="text-xs font-bold text-white tracking-widest uppercase">MTD Production Report</h2>
                </div>
                <div class="divide-y divide-slate-100 overflow-x-auto">
                    @foreach($dashboard['sites'] as $s)
                    <div class="flex items-stretch min-h-[58px]">
                        <div class="w-[60px] sm:w-[72px] flex-shrink-0 flex items-center justify-center
                                    bg-itm-blue m-2 mr-0 rounded-l-xl">
                            <span class="text-white font-black text-xs sm:text-sm tracking-wider">{{ $s['code'] }}</span>
                        </div>
                        <div class="flex-1 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 divide-x divide-slate-100">
                            @foreach([['Coal Winning','coal_winning','mcw'],['ROM Stock','rom_stock','rom'],['Port Stock Yard','port_stock_yard','psy'],['FC Production','fc_production','mfc']] as [$lbl,$key,$pfx])
                            <div class="px-2 sm:px-4 py-2.5 flex flex-col justify-center">
                                <p class="text-[8px] sm:text-[9px] font-semibold text-slate-400 uppercase tracking-wide leading-tight">{{ $lbl }}</p>
                                <p class="text-base sm:text-xl font-black text-itm-navy mt-1.5" id="{{ $pfx }}-{{ $s['site_id'] }}">
                                    {{ number_format($s['mtd'][$key]) }}
                                </p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                    {{-- ITM total --}}
                    <div class="flex items-stretch min-h-[58px] bg-emerald-50">
                        <div class="w-[60px] sm:w-[72px] flex-shrink-0 flex items-center justify-center bg-itm-teal m-2 mr-0 rounded-l-xl">
                            <span class="text-white font-black text-xs sm:text-sm tracking-wider">ITM</span>
                        </div>
                        <div class="flex-1 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 divide-x divide-emerald-100">
                            @foreach([['Coal Winning','coal_winning','tot-mcw'],['ROM Stock','rom_stock','tot-rom'],['Port Stock Yard','port_stock_yard','tot-psy'],['FC Production','fc_production','tot-mfc']] as [$lbl,$key,$id])
                            <div class="px-2 sm:px-4 py-2.5 flex flex-col justify-center">
                                <p class="text-[8px] sm:text-[9px] font-semibold text-itm-teal uppercase tracking-wide leading-tight">{{ $lbl }}</p>
                                <p class="text-base sm:text-xl font-black text-itm-teal mt-0.5" id="{{ $id }}">
                                    {{ number_format($dashboard['totals']['mtd'][$key]) }}
                                </p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── FC MTD by Product (Donut Charts) ─────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-itm overflow-hidden">
            <div class="px-4 py-3 bg-itm-blue">
                <h2 class="text-center text-xs font-bold text-white tracking-widest uppercase">FC MTD by Product</h2>
            </div>
            <div class="p-2 sm:p-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
                @foreach($dashboard['sites'] as $s)
                <div class="flex flex-col items-center gap-1.5 sm:gap-2">
                    <p class="text-xs sm:text-sm font-black text-itm-navy">{{ $s['code'] }}</p>
                    <div class="relative w-20 h-20 sm:w-28 sm:h-28">
                        <canvas id="donut-{{ $s['code'] }}" width="112" height="112">
                        </canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-base sm:text-xl font-black text-itm-navy leading-none"
                                  id="ach-{{ $s['site_id'] }}">{{ $s['achievement_pct'] }}%</span>
                            <span class="text-[7px] sm:text-[9px] text-slate-400 mt-0.5">Achievements</span>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-0.5 sm:gap-1 justify-center w-full">
                        @foreach($s['fc_by_sub_site'] as $sub)
                        <div class="flex items-center gap-0.5 sm:gap-1 cursor-pointer"
                             onclick="const center = document.getElementById('ach-{{ $s['site_id'] }}'); if(center) center.textContent='{{ number_format($sub['value']) }}';"> <!-- legend interaktif -->
                            <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full flex-shrink-0"
                                  style="background:{{ $sub['chart_color'] }}"></span>
                            <span class="text-[7px] sm:text-[9px] text-slate-500">{{ $sub['label'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between text-[9px] text-slate-400 px-1">
            <span><strong class="text-slate-500">ITM Production Dashboard</strong>
                  · Confidential · {{ $date->format('d M Y') }}</span>
            <span>Generated {{ now()->format('d M Y H:i:s') }} ·</span>
        </div>
    </div>{{-- /report-canvas --}}
</div>

</body>
</html>

<script>
const DASH = @json($dashboard);

/* ── 1. ANIMASI COUNTER angka saat load ───────────────────── */
function animateCounter(el, target, duration = 900) {
    const start    = 0;
    const easeOut  = t => 1 - Math.pow(1 - t, 3);
    let startTime  = null;

    const step = (ts) => {
        if (!startTime) startTime = ts;
        const elapsed  = ts - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const current  = Math.round(easeOut(progress) * target);
        el.textContent = current.toLocaleString('id-ID');
        if (progress < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
}

// Jalankan animasi counter untuk semua angka saat halaman load
document.addEventListener('DOMContentLoaded', () => {
    // Delay sedikit agar transisi CSS selesai dulu
    setTimeout(() => {
        DASH.sites.forEach(s => {
            const id = s.site_id;

            // Daily
            animCounter(`dcw-${id}`, s.daily.coal_winning,  600);
            animCounter(`dfc-${id}`, s.daily.fc_production, 700);

            // MTD
            animCounter(`mcw-${id}`, s.mtd.coal_winning,     800);
            animCounter(`rom-${id}`, s.mtd.rom_stock,         850);
            animCounter(`psy-${id}`, s.mtd.port_stock_yard,   900);
            animCounter(`mfc-${id}`, s.mtd.fc_production,     950);
        });
        // Totals
        animCounter('tot-dcw', DASH.totals.daily.coal_winning,   700);
        animCounter('tot-dfc', DASH.totals.daily.fc_production,  800);
        animCounter('tot-mcw', DASH.totals.mtd.coal_winning,     900);
        animCounter('tot-rom', DASH.totals.mtd.rom_stock,        950);
        animCounter('tot-psy', DASH.totals.mtd.port_stock_yard, 1000);
        animCounter('tot-mfc', DASH.totals.mtd.fc_production,   1050);
    }, 200);
});

function animCounter(id, target, duration) {
    const el = document.getElementById(id);
    if (el) animateCounter(el, target, duration);
}

/* ── 2. DONUT CHARTS dengan interactive popup ─────────────── */
// Track popup state per site
const popupState = {};
let activePopupSite = null;

window.addEventListener('DOMContentLoaded', () => {
    DASH.sites.forEach((s, siteIdx) => {
        const ctx = document.getElementById('donut-' + s.code);
        if (!ctx) return;

    const subs    = s.fc_by_sub_site;
    const hasData = subs.length > 0 && subs.some(x => x.value > 0);
    const total   = subs.reduce((a, b) => a + (b.value || 0), 0);

    // Data untuk chart
    const chartData   = hasData ? subs.map(x => x.value)       : [1];
    const chartColors = hasData ? subs.map(x => x.chart_color) : ['#E2E8F0'];
    const chartLabels = hasData ? subs.map(x => x.label)       : [s.code];

    // Buat popup element
    const wrapper = ctx.closest('.relative');
    const popup   = document.createElement('div');
    popup.className   = 'donut-popup';
    popup.id          = `popup-${s.code}`;
    popup.innerHTML   = buildPopupHTML(s, subs, total);
    wrapper.style.position = 'relative';
    wrapper.appendChild(popup);
    popupState[s.code] = false;

    // Chart.js instance
    const chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: chartLabels,
            datasets: [{
                data:            chartData,
                backgroundColor: chartColors,
                borderWidth:     3,
                borderColor:     '#FFFFFF',
                hoverOffset:     8,
                hoverBorderWidth: 3,
            }]
        },
        options: {
            cutout:           '68%',
            responsive:       false,
            animation: {
                animateRotate:  true,
                animateScale:   false,
                duration:       900,
                delay:          (ctx) => ctx.dataIndex * 80 + siteIdx * 60,
                easing:         'easeOutQuart',
            },
            plugins: {
                legend:  { display: false },
                tooltip: { enabled: false }, // Kita buat custom tooltip/popup
            },
            onClick(event, elements) {
                if (!hasData) return;

                if (elements.length > 0) {
                    const index = elements[0].index;
                    const sub   = subs[index];

                    // Update text tengah (biar interaktif kayak contoh kamu)
                    const center = document.getElementById(`ach-${s.site_id}`);
                    if (center) {
                        center.textContent = sub.value.toLocaleString('id-ID');
                    }
                }

                // tetap jalankan popup
                const isOpen = popupState[s.code];

                DASH.sites.forEach(otherSite => {
                    if (otherSite.code !== s.code) {
                        closePopup(otherSite.code);
                    }
                });

                if (isOpen) {
                    closePopup(s.code);
                } else {
                    openPopup(s.code, elements, s, subs, total);
                }
            },
        },
    });

    // Simpan referensi chart
    window[`chart_${s.code}`] = chart;
});
});

function buildPopupHTML(site, subs, total) {
    const plan = site.plan || 0;
    const achPct = site.achievement_pct || 0;

    let rows = '';
    subs.forEach(sub => {
        const pct = total > 0 ? ((sub.value / total) * 100).toFixed(1) : '0';
        rows += `
            <div class="donut-popup-row">
                <div class="donut-popup-dot" style="background:${sub.chart_color}"></div>
                <span class="donut-popup-label">${sub.label}</span>
                <span class="donut-popup-value">${sub.value.toLocaleString('id-ID')}</span>
                <span class="donut-popup-pct">${pct}%</span>
            </div>`;
    });

    // Baris total
    rows += `
        <div style="border-top:1px solid rgba(255,255,255,0.1);margin-top:5px;padding-top:5px">
            <div class="donut-popup-row">
                <div class="donut-popup-dot" style="background:#1D9E75"></div>
                <span class="donut-popup-label" style="font-weight:700;color:rgba(255,255,255,0.85)">Total MTD</span>
                <span class="donut-popup-value" style="color:#34D399">${total.toLocaleString('id-ID')}</span>
            </div>
            ${plan > 0 ? `
            <div class="donut-popup-row">
                <div class="donut-popup-dot" style="background:#60A5FA"></div>
                <span class="donut-popup-label">Achievement</span>
                <span class="donut-popup-value" style="color:#60A5FA">${achPct}%</span>
            </div>` : ''}
        </div>`;

    return `<div class="donut-popup-title">${site.code} — FC MTD by Product</div>${rows}`;
}

function openPopup(siteCode, elements, site, subs, total) {
    const popup = document.getElementById(`popup-${siteCode}`);
    if (!popup) return;

    // Update konten (refresh nilai terbaru)
    popup.innerHTML = buildPopupHTML(site, subs, total);
    popup.classList.add('show');
    popupState[siteCode] = true;
    activePopupSite = siteCode;
}

function closePopup(siteCode) {
    const popup = document.getElementById(`popup-${siteCode}`);
    if (!popup) return;
    popup.classList.remove('show');
    popupState[siteCode] = false;
    if (activePopupSite === siteCode) activePopupSite = null;

    // Reset teks tengah kembali ke persen achievement
    const site = DASH.sites.find(s => s.code === siteCode);
    if (site) {
        const ach = document.getElementById(`ach-${site.site_id}`);
        if (ach) ach.textContent = site.achievement_pct + '%';
    }
}

// Klik di luar popup → tutup semua
document.addEventListener('click', (e) => {
    if (!activePopupSite) return;
    const canvas = document.getElementById('donut-' + activePopupSite);
    if (canvas && !canvas.contains(e.target)) {
        closePopup(activePopupSite);
    }
});

// Klik di luar area donut → reset semua teks tengah ke persen
document.addEventListener('click', (e) => {
    const donutContainer = document.querySelector('.grid.grid-cols-2.sm\\:grid-cols-3.md\\:grid-cols-4.lg\\:grid-cols-6');
    if (donutContainer && !donutContainer.contains(e.target)) {
        DASH.sites.forEach(s => {
            const ach = document.getElementById(`ach-${s.site_id}`);
            if (ach) ach.textContent = s.achievement_pct + '%';
        });
    }
});

/* ── 3. REALTIME — Echo + polling ─────────────────────────── */
const reportDate = '{{ $date->format("Y-m-d") }}';

if (window.Echo) {
    window.Echo.channel(`itm-dashboard.${reportDate}`)
        .listen('.DashboardUpdated', e => {
            if (e.payload) applyUpdate(e.payload, true);
        });
}

setInterval(() => {
    fetch(`/api/dashboard/data?date=${reportDate}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' }
    })
    .then(r => r.json())
    .then(d => applyUpdate(d, false))
    .catch(console.error);
}, 120_000);

function applyUpdate(data, animate = false) {
    data.sites.forEach(s => {
        const id  = s.site_id;
        const dur = animate ? 600 : 0;

        const setEl = (elId, val) => {
            const el = document.getElementById(elId);
            if (!el) return;
            if (animate) {
                animateCounter(el, val, dur);
            } else {
                el.textContent = Math.round(val).toLocaleString('id-ID');
            }
        };

        setEl(`dcw-${id}`, s.daily.coal_winning);
        setEl(`dfc-${id}`, s.daily.fc_production);
        setEl(`mcw-${id}`, s.mtd.coal_winning);
        setEl(`rom-${id}`, s.mtd.rom_stock);
        setEl(`psy-${id}`, s.mtd.port_stock_yard);
        setEl(`mfc-${id}`, s.mtd.fc_production);

        const ach = document.getElementById(`ach-${id}`);
        if (ach) ach.textContent = s.achievement_pct + '%';
    });

    const t = data.totals;
    const setTot = (id, val) => {
        const el = document.getElementById(id);
        if (el) {
            if (animate) animateCounter(el, val, 700);
            else el.textContent = Math.round(val).toLocaleString('id-ID');
        }
    };
    setTot('tot-dcw', t.daily.coal_winning);
    setTot('tot-dfc', t.daily.fc_production);
    setTot('tot-mcw', t.mtd.coal_winning);
    setTot('tot-rom', t.mtd.rom_stock);
    setTot('tot-psy', t.mtd.port_stock_yard);
    setTot('tot-mfc', t.mtd.fc_production);

    const lu = document.getElementById('last-update');
    if (lu) lu.textContent = data.last_input;
}

/* ── 4. EXPORT ─────────────────────────────────────────────── */
async function exportDash(format) {
    const el     = document.getElementById('report-canvas');
    const canvas = await html2canvas(el, {
        scale:           2,
        useCORS:         true,
        backgroundColor: format === 'jpg' ? '#ffffff' : '#EFF4FB',
        logging:         false,
        onclone(doc) {
            // Tutup semua popup sebelum capture
            doc.querySelectorAll('.donut-popup').forEach(p => p.classList.remove('show'));
        }
    });
    const a       = document.createElement('a');
    a.href        = canvas.toDataURL(format === 'jpg' ? 'image/jpeg' : 'image/png', 0.92);
    a.download    = `ITM_Daily_Report_{{ $date->format('Ymd') }}.${format}`;
    a.click();
}
</script>
