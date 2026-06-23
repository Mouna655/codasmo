<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ITM Production Dashboard</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('img/ITM_Logo_3.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ── Keyframes ─────────────────────────────────────────── */
        @keyframes fadeUp   { from{opacity:0;transform:translateY(32px)} to{opacity:1;transform:translateY(0)} }
        @keyframes fadeIn   { from{opacity:0} to{opacity:1} }
        @keyframes pulse    { 0%,100%{opacity:1} 50%{opacity:.4} }
        @keyframes gridMove { from{transform:translateY(0)} to{transform:translateY(32px)} }
        @keyframes barGrow  { from{width:0} to{width:var(--w)} }

        html { scroll-behavior:smooth; }

        /* ── Scroll reveal ─────────────────────────────────────── */
        .reveal { opacity:0; transform:translateY(24px); transition:opacity .7s ease,transform .7s ease; }
        .reveal.visible { opacity:1; transform:translateY(0); }

        /* ── Navbar ─────────────────────────────────────────────── */
        .nav {
            position:sticky; top:0; z-index:100;
            background:rgba(13,27,94,.88); backdrop-filter:blur(16px);
            border-bottom:1px solid rgba(255,255,255,.08);
            padding:0 32px; height:64px;
            display:flex; align-items:center; justify-content:space-between;
        }
        .nav-logo { display:flex; align-items:center; gap:10px; }
        .nav-logo-box {
            width:64px; height:64px;
            border-radius:10px; display:flex; align-items:center; justify-content:center;
        }
        .nav-logo-box svg { width:20px; height:20px; stroke:white; fill:none; stroke-width:1.5; }
        .nav-links { display:flex; align-items:center; gap:4px; }
        .nav-link {
            padding:6px 14px; border-radius:8px; font-size:12px; font-weight:600;
            color:rgba(255,255,255,.7); text-decoration:none; transition:all .2s;
        }
        .nav-link:hover { background:rgba(255,255,255,.1); color:white; }
        .nav-btn {
            padding:7px 16px; border-radius:9px; font-size:12px; font-weight:700;
            background:linear-gradient(135deg,#2563EB,#1B2A8A); color:white;
            text-decoration:none; transition:all .2s;
        }
        .nav-btn:hover { transform:translateY(-1px); box-shadow:0 4px 16px rgba(37,99,235,.5); }

        /* ── Hero ───────────────────────────────────────────────── */
        .hero {
            position:relative; min-height:600px;
            background:linear-gradient(160deg,rgba(13,27,94,.7) 0%,rgba(27,42,138,.7) 45%,rgba(15,110,86,.7) 100%), url('/img/bg-1.png') center/cover no-repeat;
            display:flex; flex-direction:column; align-items:center; justify-content:center;
            padding:80px 32px 60px; text-align:center; overflow:hidden;
        }
        .hero-grid {
            position:absolute; inset:0; opacity:.1;
            background-image:
                linear-gradient(rgba(255,255,255,.4) 1px,transparent 1px),
                linear-gradient(90deg,rgba(255,255,255,.4) 1px,transparent 1px);
            background-size:40px 40px;
            animation:gridMove 4s linear infinite;
        }
        .hero-glow {
            position:absolute; width:600px; height:600px;
            background:radial-gradient(circle,rgba(37,99,235,.25) 0%,transparent 70%);
            border-radius:50%; top:50%; left:50%; transform:translate(-50%,-50%);
            animation:pulse 4s ease-in-out infinite;
        }
        .hero-badge {
            display:inline-flex; align-items:center; gap:6px;
            background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2);
            border-radius:999px; padding:6px 16px; margin-bottom:24px;
            animation:fadeUp .6s ease forwards;
        }
        .hero-badge-dot { width:7px;height:7px;background:#34d399;border-radius:50%;animation:pulse 2s infinite; }
        .hero-badge span { font-size:11px; font-weight:600; color:rgba(255,255,255,.9); }
        .hero-title {
            font-size:clamp(32px,5vw,52px); font-weight:900; color:white;
            line-height:1.05; margin:0 0 16px;
            animation:fadeUp .7s .1s ease both;
        }
        .hero-title .gradient-text {
            background:linear-gradient(135deg,#60A5FA,#34d399);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
        }
        .hero-sub {
            font-size:15px; color:rgba(255,255,255,.72); max-width:520px;
            margin:0 auto 36px; line-height:1.7;
            animation:fadeUp .7s .2s ease both;
        }
        .hero-cta-row {
            display:flex; gap:12px; justify-content:center; flex-wrap:wrap;
            animation:fadeUp .7s .3s ease both;
        }
        .cta-btn {
            display:inline-flex; align-items:center; gap:8px;
            padding:13px 26px; border-radius:13px; font-size:13px; font-weight:700;
            text-decoration:none; transition:all .25s; border:none; cursor:pointer;
        }
        .cta-btn svg { width:16px; height:16px; stroke:currentColor; fill:none; stroke-width:2; }
        .cta-primary-btn {
            background:linear-gradient(135deg,#2563EB,#1D4ED8); color:white;
        }
        .cta-primary-btn:hover { transform:translateY(-2px); box-shadow:0 8px 28px rgba(37,99,235,.5); }
        .cta-ghost-btn {
            background:rgba(255,255,255,.12); color:white; border:1px solid rgba(255,255,255,.2);
        }
        .cta-ghost-btn:hover { background:rgba(255,255,255,.2); transform:translateY(-2px); }

        /* ── Stats bar ──────────────────────────────────────────── */
        .stats-bar {
            background:white; display:flex; flex-wrap:wrap;
            justify-content:center; border-bottom:1px solid #e2e8f0;
            animation:fadeIn .8s .4s ease both;
        }
        .stat-item { padding:20px 36px; text-align:center; border-right:1px solid #f1f5f9; }
        .stat-item:last-child { border-right:none; }
        .stat-num { font-size:26px; font-weight:900; color:#1B2A8A; line-height:1; }
        .stat-lbl { font-size:10px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:.06em; margin-top:4px; }

        /* ── Dashboard cards ─────────────────────────────────────── */
        .dash-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(340px,1fr)); gap:20px; margin-top:40px; }
        .dash-card {
            border-radius:24px; overflow:hidden; text-decoration:none;
            border:2px solid transparent; transition:all .3s cubic-bezier(.34,1.56,.64,1);
            display:block; box-shadow:0 4px 24px rgba(27,42,138,.12);
        }
        .dash-card:hover { transform:translateY(-8px) scale(1.01); }
        .dash-card.card-daily:hover  { border-color:#2563EB; box-shadow:0 20px 60px rgba(37,99,235,.3); }
        .dash-card.card-party:hover { border-color:#1D9E75; box-shadow:0 20px 60px rgba(29, 158, 117, 0.3); }
        .dash-card.card-poa:hover { border-color:#F7FF19AB; box-shadow:0 20px 60px rgba(192, 197, 50, 0.66); }
        .dash-card.card-weekly:hover { border-color:#53CBF3; box-shadow:0 20px 60px rgba(34, 110, 135, 0.66); }
        .dash-card-hd {
            padding:28px; position:relative; overflow:hidden;
        }
        .dash-card-hd::before {
            content:''; position:absolute; inset:0; opacity:.1;
            background-image:linear-gradient(rgba(255,255,255,.5) 1px,transparent 1px),
                             linear-gradient(90deg,rgba(255,255,255,.5) 1px,transparent 1px);
            background-size:24px 24px;
        }
        .card-daily  .dash-card-hd { background:linear-gradient(135deg,#1B2A8A,#2563EB); }
        .card-party .dash-card-hd { background:linear-gradient(135deg,#0F6E56,#1D9E75); }
        .card-poa .dash-card-hd { background:linear-gradient(135deg,#C0C532,#F7FF19AB); }
        .card-weekly .dash-card-hd { background:linear-gradient(135deg,#226E87A8,#53CBF3); }
        .dash-card-bd { background:white; padding:22px 28px 26px; }

        /* ── About section ──────────────────────────────────────── */
        .about-grid { display:grid; grid-template-columns:1fr 1fr; gap:52px; align-items:center; }
        @media(max-width:768px){ .about-grid,.dash-grid { grid-template-columns:1fr; } }
        .about-chart {
            background:linear-gradient(135deg,#1B2A8A,#2851A3);
            border-radius:24px; padding:28px; position:relative; overflow:hidden;
        }
        .about-chart::before {
            content:''; position:absolute; inset:0; opacity:.1;
            background-image:linear-gradient(rgba(255,255,255,.4) 1px,transparent 1px),
                             linear-gradient(90deg,rgba(255,255,255,.4) 1px,transparent 1px);
            background-size:28px 28px;
        }
        .about-bar-row { display:flex; align-items:center; gap:10px; margin-bottom:10px; }
        .about-bar-lbl { font-size:10px;font-weight:700;color:rgba(255,255,255,.8);width:38px;flex-shrink:0;text-align:right; }
        .about-bar-track { flex:1; height:10px; background:rgba(255,255,255,.15); border-radius:999px; overflow:hidden; }
        .about-bar-fill { height:100%; border-radius:999px; width:0; transition:width 1.4s ease; }
        .about-bar-val { font-size:10px; font-weight:700; color:rgba(255,255,255,.6); width:34px; }

        /* ── Feature cards ──────────────────────────────────────── */
        .feat-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(230px,1fr)); gap:16px; margin-top:40px; }
        .feat-card {
            padding:24px; border-radius:18px; border:1.5px solid #f1f5f9;
            transition:all .2s; background:white;
        }
        .feat-card:hover { border-color:#BFDBFE; transform:translateY(-4px); box-shadow:0 8px 24px rgba(27,42,138,.1); }
        .feat-ico {
            width:44px; height:44px; border-radius:12px;
            display:flex; align-items:center; justify-content:center; margin-bottom:14px;
        }
        .feat-ico svg { width:22px; height:22px; stroke:currentColor; fill:none; stroke-width:1.5; }

        /* ── Section helper ─────────────────────────────────────── */
        .section-wrap { max-width:1100px; margin:0 auto; padding:72px 32px; }
        .section-badge {
            display:inline-flex; align-items:center; gap:6px; background:#EFF4FB;
            border-radius:999px; padding:4px 12px; margin-bottom:14px;
        }
        .section-badge span { font-size:10px; font-weight:700; color:#1B2A8A; text-transform:uppercase; letter-spacing:.06em; }

        /* ── Footer ─────────────────────────────────────────────── */
        .footer { background:linear-gradient(135deg,#0D1B5E,#1B2A8A); padding:48px 32px 24px; }
        .footer-inner { max-width:1100px; margin:0 auto; }
        .footer-grid { display:grid; grid-template-columns:2fr 1fr 1fr; gap:40px; margin-bottom:36px; }
        @media(max-width:640px){ .footer-grid { grid-template-columns:1fr; } }
        .footer-lbl { font-size:10px; font-weight:700; color:rgba(255,255,255,.4); text-transform:uppercase; letter-spacing:.07em; margin-bottom:12px; }
        .footer-link { font-size:12px; color:rgba(255,255,255,.6); text-decoration:none; display:block; margin-bottom:8px; transition:color .15s; }
        .footer-link:hover { color:white; }
        .footer-divider { border:none; border-top:1px solid rgba(255,255,255,.1); margin:0 0 20px; }
        .footer-bottom { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; }
        .footer-bottom p { font-size:11px; color:rgba(255,255,255,.35); margin:0; }
    </style>
</head>
<body style="background:#0D1B5E;font-family:'Inter',sans-serif;overflow-x:hidden">

{{-- ═══════════════════════════════════════════════════════
     NAVBAR
═══════════════════════════════════════════════════════ --}}
<nav class="nav">
    <div class="nav-logo">
        <div class="nav-logo-box">
            <img src="{{ asset('img/ITM_Logo_3.png') }}" alt="ITM Logo" style="width:100%;height:100%;object-fit:contain;border-radius:12px;margin-bottom:10px;padding:6px;">
        </div>
        <div>
            <p style="font-size:14px;font-weight:900;color:white;margin:0;line-height:1">PT Indo Tambangraya Megah Tbk</p>
            <p style="font-size:9px;color:rgba(255,255,255,.45);margin:2px 0 0">Dashboard Monitoring</p>
        </div>
    </div>
    <div class="nav-links">
        <a href="#tentang" class="nav-link">About</a>
        <a href="#dashboard" class="nav-link">Dashboard</a>
        <a href="#fitur" class="nav-link">Features</a>
        <a href="{{ route('admin.access') }}" class="nav-btn">Admin Panel</a>
    </div>
</nav>

{{-- ═══════════════════════════════════════════════════════
     HERO
═══════════════════════════════════════════════════════ --}}
<div class="hero">
    <div class="hero-grid"></div>
    <div class="hero-glow"></div>

    {{-- Decorative rings --}}
    <svg style="position:absolute;width:600px;height:600px;top:50%;left:50%;
                transform:translate(-50%,-50%);opacity:.06;pointer-events:none" viewBox="0 0 600 600">
        <circle cx="300" cy="300" r="140" fill="none" stroke="white" stroke-width="1"/>
        <circle cx="300" cy="300" r="220" fill="none" stroke="white" stroke-width="1"/>
        <circle cx="300" cy="300" r="290" fill="none" stroke="white" stroke-width="1"/>
    </svg>

    <div style="position:relative;z-index:1;width:100%">
        {{-- Badge --}}
        <div class="hero-badge">
            <span class="hero-badge-dot"></span>
            <span>
                @if($latestDate)
                    Lastest Data: {{ \Carbon\Carbon::parse($latestDate)->translatedFormat('d F Y') }}
                @else
                    System Active
                @endif
            </span>
        </div>

        {{-- Title --}}
        <h1 class="hero-title">
            Monitoring Coal Production<br>
            <span class="gradient-text">In Near Real-time</span>
        </h1>

        {{-- Subtitle --}}
        <p class="hero-sub">
            Daily production dashboard for PT Indo Tambangraya Megah Tbk.<br>
            Accurate data, interactive visualization, and accessible anytime without login.
        </p>

        {{-- CTAs --}}
        <div class="hero-cta-row">
            <a href="{{ route('public.daily') }}" class="cta-btn cta-primary-btn">
                <svg viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75z"/>
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625z"/>
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                </svg>
                View Dashboard
            </a>
            <a href="#tentang" class="cta-btn cta-ghost-btn">
                <svg viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                </svg>
                About The System
            </a>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     STATS BAR
═══════════════════════════════════════════════════════ --}}
<div class="stats-bar">
    @if($summary)
    <div class="stat-item">
        <div class="stat-num js-count" data-target="{{ $summary['totals']['daily']['coal_winning'] }}">0</div>
        <div class="stat-lbl">Coal Winning Daily (tons)</div>
    </div>
    <div class="stat-item">
        <div class="stat-num js-count" data-target="{{ $summary['totals']['daily']['fc_production'] }}">0</div>
        <div class="stat-lbl">FC Production Daily (tons)</div>
    </div>
    <div class="stat-item">
        <div class="stat-num js-count" data-target="{{ $summary['totals']['mtd']['fc_production'] }}">0</div>
        <div class="stat-lbl">FC Production MTD (tons)</div>
    </div>
    <div class="stat-item">
        <div class="stat-num" style="color:#D97706">{{ $summary['totals']['achievement_pct'] }}%</div>
        <div class="stat-lbl">Achievement ITM</div>
    </div>
    @endif
    <div class="stat-item">
        <div class="stat-num" style="font-size:20px">6 Site</div>
        <div class="stat-lbl">Operational Sites</div>
    </div>
    <div class="stat-item">
        <div class="stat-num" style="color:#1D9E75;display:flex;align-items:center;justify-content:center;gap:6px">
            <span style="width:8px;height:8px;background:#1D9E75;border-radius:50%;
                         display:inline-block;animation:pulse 2s infinite;flex-shrink:0"></span>
            Live
        </div>
        <div class="stat-lbl">Data Status</div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     DASHBOARD SELECTION
═══════════════════════════════════════════════════════ --}}
<div id="dashboard" style="background:#f8fafc;padding:72px 0">
<div class="section-wrap" style="padding-top:0;padding-bottom:0">

    <div class="reveal">
        <div class="section-badge">
            <!-- <svg style="width:12px;height:12px;stroke:#1B2A8A;fill:none;stroke-width:2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75z"/>
            </svg> -->
            <span>Dashboard Portal</span>
        </div>
        <h2 style="font-size:32px;font-weight:900;color:#0F172A;margin:0 0 12px;line-height:1.1">
            Select the Dashboard You Want to View
        </h2>
        <p style="font-size:15px;color:#64748b;max-width:540px;line-height:1.7;margin:0">
            All dashboards are accessible without login. Data is automatically updated whenever operators upload the daily Excel report.
        </p>
    </div>

    <div class="dash-grid reveal" style="transition-delay:.1s">

        {{-- ── Card: Daily Dashboard ── --}}
        <a href="{{ route('public.daily') }}" class="dash-card card-daily">
            <div class="dash-card-hd">
                <div style="width:52px;height:52px;background:rgba(255,255,255,.2);border-radius:16px;
                            display:flex;align-items:center;justify-content:center;margin-bottom:18px">
                    <svg style="width:28px;height:28px;stroke:white;fill:none;stroke-width:1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                    </svg>
                </div>
                <div style="display:inline-flex;align-items:center;gap:5px;background:rgba(255,255,255,.2);
                            border-radius:999px;padding:4px 10px;margin-bottom:10px">
                    <span style="width:5px;height:5px;background:#34d399;border-radius:50%;
                                 display:inline-block;animation:pulse 2s infinite"></span>
                    <span style="font-size:9px;font-weight:700;color:white;text-transform:uppercase;letter-spacing:.06em">Live</span>
                </div>
                <h3 style="font-size:22px;font-weight:900;color:white;margin:0 0 6px">Daily Dashboard</h3>
                <p style="font-size:12px;color:rgba(255, 255, 255, 0.75);margin:0;line-height:1.5">
                        Daily production report — Coal Winning, FC Production, ROM Stock & Port Stock Yard per site.
                </p>
            </div>
            <div class="dash-card-bd">
                @if($summary)
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px">
                    @foreach([
                        ['Coal Winning (Daily)', $summary['totals']['daily']['coal_winning'], '#1B2A8A'],
                        ['FC Production (Daily)', $summary['totals']['daily']['fc_production'], '#2563EB'],
                        ['FC Production (MTD)', $summary['totals']['mtd']['fc_production'], '#1B2A8A'],
                        ['Achievement', $summary['totals']['achievement_pct'].'%', '#D97706'],
                    ] as [$lbl,$val,$color])
                    <div style="background:#f8fafc;border-radius:12px;padding:12px 14px">
                        <p style="font-size:8px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin:0 0 3px">{{ $lbl }}</p>
                        <p style="font-size:15px;font-weight:900;color:{{ $color }};margin:0">
                            {{ is_numeric($val) ? number_format($val) : $val }}
                        </p>
                    </div>
                    @endforeach
                </div>
                @endif
                <div style="display:flex;flex-wrap:wrap;gap:5px;margin-top:22px;margin-bottom:18px">
                    @foreach(['IMM','TCM','BEK','GPK','JBG','TIS'] as $code)
                    <span style="font-size:9px;font-weight:700;background:#EFF4FB;color:#1B2A8A;
                                 padding:3px 8px;border-radius:6px">{{ $code }}</span>
                    @endforeach
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:32px">
                    <span style="font-size:11px;color:#94a3b8">
                        Data: {{ $latestDate ? \Carbon\Carbon::parse($latestDate)->format('d M Y') : '-' }}
                    </span>
                    <span style="display:flex;align-items:center;gap:4px;font-size:12px;font-weight:800;color:#1B2A8A">
                        Open Dashboard
                        <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                        </svg>
                    </span>
                </div>
            </div>
        </a>

        {{-- ── Card: 3rd Party Dashboard ── --}}
        <a href="{{ route('public.third-party') }}" class="dash-card card-party">

            {{-- ═══ CARD HEADER ═══ --}}
            <div class="dash-card-hd">

                {{-- Icon --}}
                <div style="width:52px;height:52px;background:rgba(255,255,255,.2);border-radius:16px;
                            display:flex;align-items:center;justify-content:center;margin-bottom:18px">
                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24">
                        <path fill="#fff"
                            d="M6 20c1.1 0 2-.9 2-2v-7c0-1.1-.9-2-2-2s-2 .9-2 2v7c0 1.1.9 2 2 2
                                m10-5v3c0 1.1.9 2 2 2s2-.9 2-2v-3c0-1.1-.9-2-2-2s-2 .9-2 2
                                m-4 5c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2s-2 .9-2 2v12c0 1.1.9 2 2 2"/>
                    </svg>
                </div>

                {{-- Status badge --}}
                <div style="display:inline-flex;align-items:center;gap:5px;background:rgba(255,255,255,.2);
                            border-radius:999px;padding:4px 10px;margin-bottom:10px">
                    @if($thirdPartyPreview['has_data'])
                        <span style="width:5px;height:5px;background:#34d399;border-radius:50%;
                                    display:inline-block;animation:pulse 2s infinite"></span>
                        <span style="font-size:9px;font-weight:700;color:white;text-transform:uppercase;
                                    letter-spacing:.06em">
                            live
                        </span>
                    @else
                        <span style="font-size:9px;font-weight:700;color:rgba(255,255,255,.7);
                                    text-transform:uppercase;letter-spacing:.06em">No Data</span>
                    @endif
                </div>

                <h3 style="font-size:22px;font-weight:900;color:white;margin:0 0 6px">
                    3rd Party Dashboard
                </h3>
                <p style="font-size:12px;color:rgba(255,255,255,.75);margin:0;line-height:1.5">
                    3rd party coal report &amp; Achieve vs Plan, supply by quality, and shipper performance.
                </p>

                {{-- YTD summary di header --}}
                <!-- @if($thirdPartyPreview['has_data'])
                @php
                    $achVal    = $thirdPartyPreview['ytd_achieve'];
                    $ringColor = $achVal >= 90 ? '#34d399' : ($achVal >= 70 ? '#FCD34D' : '#F87171');
                    // Circumference r=16 = 100.53
                    $dashArr   = round(min($achVal, 100) * 100.53 / 100, 1);
                @endphp
                <div style="margin-top:16px;display:flex;align-items:center;gap:12px">

                    Donut ring
                    <div style="position:relative;width:60px;height:60px;flex-shrink:0">
                        <svg viewBox="0 0 36 36" style="width:60px;height:60px;transform:rotate(-90deg)">
                            <circle cx="18" cy="18" r="16" fill="none"
                                    stroke="rgba(255,255,255,.2)" stroke-width="3.5"/>
                            <circle cx="18" cy="18" r="16" fill="none"
                                    stroke="{{ $ringColor }}" stroke-width="3.5"
                                    stroke-dasharray="{{ $dashArr }} 100.53"
                                    stroke-linecap="round"/>
                        </svg>
                        <div style="position:absolute;inset:0;display:flex;flex-direction:column;
                                    align-items:center;justify-content:center">
                            <span style="font-size:12px;font-weight:900;color:{{ $ringColor }};
                                        line-height:1">{{ $achVal }}%</span>
                            <span style="font-size:7px;color:rgba(255,255,255,.55);margin-top:1px">YTD</span>
                        </div>
                    </div>

                   
                </div>
                @endif -->
            </div>

            {{-- ═══ CARD BODY ═══ --}}
            <div class="dash-card-bd">

                @if($thirdPartyPreview['has_data'])

                {{-- ── Achieve to Plan by Quality (ICI 1–5) ── --}}
                <div style="margin-bottom:14px">
                    <p style="font-size:8px;font-weight:700;color:#94a3b8;text-transform:uppercase;
                            letter-spacing:.06em;margin:0 0 8px">Achieve to Plan by Quality</p>
                    @foreach($thirdPartyPreview['by_quality'] as $q)
                    @php $barW = min($q['ach'], 100); @endphp
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                        {{-- Label kualitas dengan warna ICI --}}
                        <div style="width:42px;flex-shrink:0;display:flex;align-items:center;gap:4px">
                            <span style="width:8px;height:8px;border-radius:2px;flex-shrink:0;
                                        background:{{ $q['color'] }};display:inline-block"></span>
                            <span style="font-size:9px;font-weight:800;color:#374151">
                                {{ $q['quality'] }}
                            </span>
                        </div>
                        {{-- Progress bar --}}
                        <div style="flex:1;height:8px;background:#f1f5f9;border-radius:999px;overflow:hidden">
                            <div style="height:100%;width:{{ $barW }}%;background:{{ $q['ach_color'] }};
                                        border-radius:999px;transition:width 1.2s ease"></div>
                        </div>
                        {{-- Achieve % --}}
                        <span style="width:38px;font-size:9px;font-weight:900;
                                    color:{{ $q['ach_color'] }};text-align:right;flex-shrink:0">
                            {{ $q['ach'] }}%
                        </span>
                    </div>
                    @endforeach
                </div>

                {{-- ── Divider ── --}}
                <div style="border-top:1px solid #f1f5f9;margin-bottom:12px"></div>

                {{-- ── Top 5 Shippers by Actual ── --}}
                <!-- <div style="margin-bottom:14px">
                    <p style="font-size:8px;font-weight:700;color:#94a3b8;text-transform:uppercase;
                            letter-spacing:.06em;margin:0 0 8px">
                        YTD Achievement by Shipper (Top {{ count($thirdPartyPreview['by_shipper']) }})
                    </p>
                    @foreach($thirdPartyPreview['by_shipper'] as $idx => $s)
                    @php $barW = min($s['ach'], 100); @endphp
                    <div style="display:flex;align-items:center;gap:7px;
                                margin-bottom:{{ $idx < count($thirdPartyPreview['by_shipper']) - 1 ? '5' : '0' }}px">
                        {{-- Shipper name --}}
                        <span style="width:38px;font-size:9px;font-weight:800;color:#1B2A8A;
                                    flex-shrink:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                            title="{{ $s['shipper'] }}">
                            {{ $s['shipper'] }}
                        </span>
                        {{-- Bar --}}
                        <div style="flex:1;height:6px;background:#f1f5f9;border-radius:999px;overflow:hidden">
                            <div style="height:100%;width:{{ $barW }}%;background:{{ $s['ach_color'] }};
                                        border-radius:999px"></div>
                        </div>
                        {{-- Actual tonnage --}}
                        <span style="width:34px;font-size:9px;color:#64748b;text-align:right;flex-shrink:0">
                            {{ number_format($s['actual'] / 1000, 0) }}K
                        </span>
                        {{-- Achievement % --}}
                        <span style="width:36px;font-size:9px;font-weight:900;
                                    color:{{ $s['ach_color'] }};text-align:right;flex-shrink:0">
                            {{ $s['ach'] }}%
                        </span>
                    </div>
                    @endforeach
                </div> -->

                {{-- ── Plan vs Actual KPI + Last Update ── --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px">
                    <div style="background:#f8fafc;border-radius:10px;padding:10px 12px">
                        <p style="font-size:8px;font-weight:600;color:#94a3b8;text-transform:uppercase;
                                letter-spacing:.05em;margin:0 0 3px">Total Actual</p>
                        <p style="font-size:14px;font-weight:900;color:#1D9E75;margin:0;line-height:1.1">
                            {{ $thirdPartyPreview['sum_actual_k'] }}
                            <span style="font-size:9px;color:#94a3b8;font-weight:600">ton</span>
                        </p>
                    </div>
                    <div style="background:#f8fafc;border-radius:10px;padding:10px 12px">
                        <p style="font-size:8px;font-weight:600;color:#94a3b8;text-transform:uppercase;
                                letter-spacing:.05em;margin:0 0 3px">Total Plan</p>
                        <p style="font-size:14px;font-weight:900;color:#1B2A8A;margin:0;line-height:1.1">
                            {{ number_format($thirdPartyPreview['total_plan']) }}
                            <span style="font-size:9px;color:#94a3b8;font-weight:600">ton</span>
                        </p>
                    </div>
                </div>

                {{-- ── Footer ── --}}
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <span style="font-size:11px;color:#94a3b8">
                        Last update: {{ $thirdPartyPreview['last_update'] }}
                    </span>
                    <span style="display:flex;align-items:center;gap:4px;font-size:12px;font-weight:800;
                                color:#1D9E75">
                        Open Dashboard
                        <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                        </svg>
                    </span>
                </div>

                @else

                {{-- Empty state --}}
                <div style="background:#f0fdf9;border-radius:14px;padding:20px;text-align:center;
                            margin-bottom:16px;border:1.5px dashed #A7F3D0">
                    <svg style="width:32px;height:32px;stroke:#1D9E75;fill:none;stroke-width:1.5;
                                display:block;margin:0 auto 8px" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p style="font-size:12px;font-weight:700;color:#1D9E75;margin:0">No data available yet</p>
                    <p style="font-size:10px;color:#94a3b8;margin:4px 0 0">Upload loading data in Admin Panel</p>
                </div>
                <div style="display:flex;justify-content:flex-end">
                    <span style="display:flex;align-items:center;gap:4px;font-size:12px;font-weight:800;
                                color:#1D9E75">
                        Open Dashboard
                        <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                        </svg>
                    </span>
                </div>

                @endif
            </div>
        </a>

        <!-- Card Weekly -->
        {{-- ── Card: Weekly Dashboard ── --}}
        <a href="{{ route('public.loading') }}" class="dash-card card-weekly">
            <div class="dash-card-hd">
                <div style="width:52px;height:52px;background:rgba(255,255,255,.2);border-radius:16px;
                            display:flex;align-items:center;justify-content:center;margin-bottom:18px">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 16 16">
                        <path fill="#fff" d="M14.5 16h-13C.67 16 0 15.33 0 14.5v-12C0 1.67.67 1 1.5 1h13c.83 0 1.5.67 1.5 1.5v12c0 .83-.67 1.5-1.5 1.5M1.5 2c-.28 0-.5.22-.5.5v12c0 .28.22.5.5.5h13c.28 0 .5-.22.5-.5v-12c0-.28-.22-.5-.5-.5z"/><path fill="#fff" d="M4.5 4c-.28 0-.5-.22-.5-.5v-3c0-.28.22-.5.5-.5s.5.22.5.5v3c0 .28-.22.5-.5.5m7 0c-.28 0-.5-.22-.5-.5v-3c0-.28.22-.5.5-.5s.5.22.5.5v3c0 .28-.22.5-.5.5m4 2H.5C.22 6 0 5.78 0 5.5S.22 5 .5 5h15c.28 0 .5.22.5.5s-.22.5-.5.5"/><circle cx="4" cy="9" r="1" fill="#fff"/><circle cx="8" cy="9" r="1" fill="#fff"/><circle cx="12" cy="9" r="1" fill="#fff"/><circle cx="4" cy="12" r="1" fill="#fff"/><circle cx="8" cy="12" r="1" fill="#fff"/><circle cx="12" cy="12" r="1" fill="#fff"/>
                    </svg>
                </div>

                {{-- Status badge --}}
                <div style="display:inline-flex;align-items:center;gap:5px;background:rgba(255,255,255,.2);
                            border-radius:999px;padding:4px 10px;margin-bottom:10px">
                    @if($loadingPreview['has_data'])
                        <span style="width:5px;height:5px;background:#34d399;border-radius:50%;
                                    display:inline-block;animation:pulse 2s infinite"></span>
                        <span style="font-size:9px;font-weight:700;color:white;text-transform:uppercase;letter-spacing:.06em">Live</span>
                    @else
                        <span style="font-size:9px;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.06em">No Data</span>
                    @endif
                </div>

                <h3 style="font-size:22px;font-weight:900;color:white;margin:0 0 6px">Shipment Monitoring Dashboard</h3>
                <p style="font-size:12px;color:rgba(255,255,255,.75);margin:0;line-height:1.5">
                    Shipment monitoring — BoCT, Mahakam, Overall loading status &amp; tonnage summary.
                </p>

                
            </div>

            <div class="dash-card-bd">
                @if($loadingPreview['has_data'])

                {{-- 4 KPI Status --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px">
                    @foreach([
                        ['Completed',   $loadingPreview['kpi']['Completed'],   '#06B6D4'],
                        ['In Progress', $loadingPreview['kpi']['In Progress'], '#F59E0B'],
                        ['Loading',     $loadingPreview['kpi']['Loading'],     '#F97316'],
                        ['Plan',        $loadingPreview['kpi']['Plan'],        '#EC4899'],
                    ] as [$label, $val, $color])
                    <div style="background:#f8fafc;border-radius:10px;padding:12px 12px;
                                border-left:3px solid {{ $color }}">
                        <p style="font-size:8px;font-weight:600;color:#94a3b8;text-transform:uppercase;
                                letter-spacing:.05em;margin:0 0 3px">{{ $label }}</p>
                        <p style="font-size:14px;font-weight:900;color:{{ $color }};margin:0">
                            {{ $val >= 1000000
                                ? number_format($val / 1000000, 1) . 'M'
                                : number_format($val / 1000, 0) . 'K' }}
                            <span style="font-size:9px;font-weight:600;color:#94a3b8">ton</span>
                        </p>
                    </div>
                    @endforeach
                </div>

                {{-- BoCT vs Mahakam split --}}
                <!-- <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;margin-bottom:14px">
                    <p style="font-size:8px;font-weight:700;color:#94a3b8;text-transform:uppercase;
                            letter-spacing:.06em;margin:0 0 8px">Load Port Distribution</p>
                    <div style="display:flex;gap:10px;align-items:center">
                        {{-- BoCT bar --}}
                        @php
                            $grand  = $loadingPreview['grand_total'] ?: 1;
                            $boctPct = round($loadingPreview['boct_total'] / $grand * 100);
                            $mahPct  = round($loadingPreview['mahakam_total'] / $grand * 100);
                        @endphp
                        <div style="flex:1">
                            <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                                <span style="font-size:9px;font-weight:700;color:#1B2A8A">BoCT</span>
                                <span style="font-size:9px;font-weight:700;color:#1B2A8A">{{ $boctPct }}%</span>
                            </div>
                            <div style="height:6px;background:#e2e8f0;border-radius:999px;overflow:hidden">
                                <div style="height:100%;width:{{ $boctPct }}%;background:#1B2A8A;
                                            border-radius:999px;transition:width 1.2s ease"></div>
                            </div>
                        </div>
                        <div style="flex:1">
                            <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                                <span style="font-size:9px;font-weight:700;color:#059669">Mahakam</span>
                                <span style="font-size:9px;font-weight:700;color:#059669">{{ $mahPct }}%</span>
                            </div>
                            <div style="height:6px;background:#e2e8f0;border-radius:999px;overflow:hidden">
                                <div style="height:100%;width:{{ $mahPct }}%;background:#059669;
                                            border-radius:999px;transition:width 1.2s ease"></div>
                            </div>
                        </div>
                    </div>
                </div> -->

                {{-- Grand total di header jika ada data --}}
                @if($loadingPreview['has_data'])
                <div style="margin-top:20px;display:flex;align-items:flex-end;gap:6px;margin-bottom:20px;">
                    <span style="font-size:28px;font-weight:900;color:#06B6D4;line-height:1">
                        {{ $loadingPreview['grand_total'] >= 1000000
                            ? number_format($loadingPreview['grand_total'] / 1000000, 2) . 'M'
                            : number_format($loadingPreview['grand_total'] / 1000, 0) . 'K' }}
                    </span>
                    <span style="font-size:11px;color:rgba(48, 48, 48, 0.65);margin-bottom:4px;font-weight:600">
                        ton · {{ $loadingPreview['month_label'] }}
                    </span>
                </div>
                @endif

                {{-- Shipment count & last update --}}
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <div style="display:flex;align-items:center;gap:12px">
                        <span style="font-size:10px;font-weight:700;color:#1B2A8A">
                             {{ $loadingPreview['vessel_count'] }} Vessel
                        </span>
                        <span style="font-size:10px;color:#94a3b8">
                            {{ $loadingPreview['total_shipment'] }} Shipments
                        </span>
                    </div>
                    <span style="display:flex;align-items:center;gap:4px;font-size:12px;font-weight:800;
                                color:#399cbda8">
                        Open Dashboard
                        <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                        </svg>
                    </span>
                </div>

                @else
                {{-- Empty state --}}
                <div style="background:#fefce8;border-radius:14px;padding:20px;text-align:center;
                            margin-bottom:16px;border:1.5px dashed #FDE047">
                    <svg style="width:32px;height:32px;stroke:#CA8A04;fill:none;stroke-width:1.5;
                                display:block;margin:0 auto 8px" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p style="font-size:12px;font-weight:700;color:#CA8A04;margin:0">No data available yet</p>
                    <p style="font-size:10px;color:#94a3b8;margin:4px 0 0">Upload loading data in Admin Panel</p>
                </div>
                <div style="display:flex;justify-content:flex-end">
                    <span style="display:flex;align-items:center;gap:4px;font-size:12px;font-weight:800;color:#B5A014">
                        Open Dashboard
                        <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                        </svg>
                    </span>
                </div>
                @endif
            </div>
        </a>

        {{-- ── Card: Previous Outlook (POA) Dashboard ── --}}
        <a href="{{ route('public.poa') }}" class="dash-card card-poa">
            <div class="dash-card-hd">
                <div style="width:52px;height:52px;background:rgba(255,255,255,.2);border-radius:16px;
                            display:flex;align-items:center;justify-content:center;margin-bottom:18px">
                    <svg fill="none" stroke="#fff" viewBox="0 0 24 24" stroke-width="1.5"
                        style="width:26px;height:26px">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                    </svg>
                </div>

                {{-- Status badge --}}
                <div style="display:inline-flex;align-items:center;gap:5px;background:rgba(255,255,255,.2);
                            border-radius:999px;padding:4px 10px;margin-bottom:10px">
                    @if($poaPreview['has_data'])
                        <span style="width:5px;height:5px;background:#34d399;border-radius:50%;
                                    display:inline-block;animation:pulse 2s infinite"></span>
                        <span style="font-size:9px;font-weight:700;color:white;text-transform:uppercase;
                                    letter-spacing:.06em">live</span>
                    @else
                        <span style="font-size:9px;font-weight:700;color:rgba(255,255,255,.7);
                                    text-transform:uppercase;letter-spacing:.06em">No Data</span>
                    @endif
                </div>

                <h3 style="font-size:22px;font-weight:900;color:white;margin:0 0 6px">
                    Previous Outlook Dashboard
                </h3>
                <p style="font-size:12px;color:rgba(255,255,255,.75);margin:0;line-height:1.5">
                    POA Report — Previous vs Outlook vs Actual by company for performance tracking.
                </p>

                {{-- Overall achievement di header --}}
                <!-- @if($poaPreview['has_data'])
                <div style="margin-top:14px;display:flex;align-items:center;gap:10px">
                    Achievement donut mini 
                    @php
                        $ach = $poaPreview['overall_ach'];
                        $achColor = $ach >= 80 ? '#34d399' : ($ach >= 50 ? '#FCD34D' : '#F87171');
                    @endphp
                    <div style="position:relative;width:52px;height:52px;flex-shrink:0">
                        <svg viewBox="0 0 36 36" style="width:52px;height:52px;transform:rotate(-90deg)">
                            <circle cx="18" cy="18" r="15" fill="none"
                                    stroke="rgba(255,255,255,.2)" stroke-width="3"/>
                            <circle cx="18" cy="18" r="15" fill="none"
                                    stroke="{{ $achColor }}" stroke-width="3"
                                    stroke-dasharray="{{ min($ach, 100) * 94.2 / 100 }} 94.2"
                                    stroke-linecap="round"/>
                        </svg>
                        <div style="position:absolute;inset:0;display:flex;align-items:center;
                                    justify-content:center;transform:rotate(0)">
                            <span style="font-size:10px;font-weight:900;color:{{ $achColor }}">{{ $ach }}%</span>
                        </div>
                    </div>
                    <div>
                        <p style="font-size:12px;font-weight:700;color:rgba(255,255,255,.9);margin:0">
                            Overall Achievement
                        </p>
                        <p style="font-size:11px;color:rgba(255,255,255,.6);margin:2px 0 0">
                            YTD {{ now()->format('M Y') }}
                        </p>
                    </div>
                </div>
                @endif -->
            </div>

            <div class="dash-card-bd">
                @if($poaPreview['has_data'])

                {{-- Achievement per company --}}
                <div style="margin-bottom:14px">
                    <p style="font-size:8px;font-weight:700;color:#94a3b8;text-transform:uppercase;
                            letter-spacing:.06em;margin:0 0 8px">Achievement by Company (YTD)</p>
                    @foreach($poaPreview['companies'] as $co)
                    @php
                        $pct   = min($co['ach'], 150); // cap visual di 150%
                        $color = $co['ach'] >= 80 ? '#059669' : ($co['ach'] >= 50 ? '#D97706' : '#DC2626');
                        $barW  = min($co['ach'], 100); // bar max 100% visual
                    @endphp
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                        <span style="width:36px;font-size:9px;font-weight:800;color:#1B2A8A;
                                    text-align:right;flex-shrink:0">{{ $co['company'] }}</span>
                        <div style="flex:1;height:7px;background:#f1f5f9;border-radius:999px;overflow:hidden">
                            <div style="height:100%;width:{{ $barW }}%;background:{{ $color }};
                                        border-radius:999px"></div>
                        </div>
                        <span style="width:36px;font-size:9px;font-weight:800;text-align:right;
                                    flex-shrink:0;color:{{ $color }}">{{ $co['ach'] }}%</span>
                    </div>
                    @endforeach
                </div>

                {{-- Outlook vs Actual summary --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px">
                    <div style="background:#f8fafc;border-radius:10px;padding:10px 12px">
                        <p style="font-size:8px;font-weight:600;color:#94a3b8;text-transform:uppercase;
                                letter-spacing:.05em;margin:0 0 3px">Total Outlook</p>
                        <p style="font-size:14px;font-weight:900;color:#F59E0B;margin:0">
                            {{ number_format($poaPreview['total_outlook'], 0) }}
                            <span style="font-size:9px;color:#94a3b8;font-weight:600">Kt</span>
                        </p>
                    </div>
                    <div style="background:#f8fafc;border-radius:10px;padding:10px 12px">
                        <p style="font-size:8px;font-weight:600;color:#94a3b8;text-transform:uppercase;
                                letter-spacing:.05em;margin:0 0 3px">Total Actual</p>
                        <p style="font-size:14px;font-weight:900;color:#059669;margin:0">
                            {{ number_format($poaPreview['total_actual'], 0) }}
                            <span style="font-size:9px;color:#94a3b8;font-weight:600">Kt</span>
                        </p>
                    </div>
                </div>

                {{-- Footer info --}}
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <span style="font-size:11px;color:#94a3b8">
                        Update: {{ $poaPreview['upload_date'] }}
                    </span>
                    <span style="display:flex;align-items:center;gap:4px;font-size:12px;font-weight:800;color:#B5A014">
                        Open Dashboard
                        <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                        </svg>
                    </span>
                </div>

                @else
                {{-- Empty state --}}
                <div style="background:#EFF4FB;border-radius:14px;padding:20px;text-align:center;
                            margin-bottom:16px;border:1.5px dashed #BFDBFE">
                    <svg style="width:32px;height:32px;stroke:#1B2A8A;fill:none;stroke-width:1.5;
                                display:block;margin:0 auto 8px" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p style="font-size:12px;font-weight:700;color:#1B2A8A;margin:0">No data available yet</p>
                    <p style="font-size:10px;color:#94a3b8;margin:4px 0 0">Upload loading data in Admin Panel</p>
                </div>
                <div style="display:flex;justify-content:flex-end">
                    <span style="display:flex;align-items:center;gap:4px;font-size:12px;font-weight:800;color:#1B2A8A">
                        Open Dashboard
                        <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                        </svg>
                    </span>
                </div>
                @endif
            </div>
        </a>

    </div>
</div>
</div>

{{-- ═══════════════════════════════════════════════════════
     ABOUT SECTION
═══════════════════════════════════════════════════════ --}}
<div id="tentang" style="background:white;padding:0">
<div class="section-wrap">
    <div class="about-grid reveal">

        {{-- Chart visual --}}
        <!-- <div class="about-chart">
            <p style="font-size:11px;font-weight:700;color:rgba(255,255,255,.6);text-align:center;
                      margin:0 0 20px;text-transform:uppercase;letter-spacing:.06em;position:relative;z-index:1">
                FC Production MTD — Target Achievement
            </p>
            <div style="position:relative;z-index:1">
                @php
                    $bars = [
                        ['IMM','#60A5FA', 52],
                        ['TCM','#34D399', 50],
                        ['BEK','#A78BFA', 53],
                        ['GPK','#FCD34D', 19],
                        ['JBG','#F9A8D4',  0],
                        ['TIS','#6EE7B7',  4],
                    ];
                @endphp
                @foreach($bars as [$lbl,$clr,$pct])
                <div class="about-bar-row">
                    <span class="about-bar-lbl">{{ $lbl }}</span>
                    <div class="about-bar-track">
                        <div class="about-bar-fill" data-width="{{ $pct }}" style="background:{{ $clr }}"></div>
                    </div>
                    <span class="about-bar-val">{{ $pct }}%</span>
                </div>
                @endforeach
            </div>
        </div> -->

        {{-- Info --}}
        <div>
            <div class="section-badge">
                <span>About the System</span>
            </div>
            <h2 style="font-size:30px;font-weight:900;color:#0F172A;margin:0 0 12px;line-height:1.1">
                Production Dashboard<br>Centralized &amp; Accurate
            </h2>
            <p style="font-size:14px;color:#64748b;line-height:1.7;margin:0 0 28px">
                This system is designed to simplify monitoring of coal production across all ITM operational sites on a daily and weekly basis.
            </p>
            <div style="display:flex;flex-direction:column;gap:18px">
                @foreach([
                    ['blue','M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                     'Data from Excel',
                     'Upload Excel file in Draft_Daily.xlsx format - the system automatically reads and processes the data.'],
                    ['teal','M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z',
                     'Real-Time Updates',
                     'Once uploades, the dashboard updates instantly.'],
                    ['amber','M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3',
                     'Export PNG &amp; JPG',
                     'Download the dashboard view as a high-resolution image for reports or presentations.'],
                ] as [$color,$path,$title,$desc])
                <div style="display:flex;gap:14px;align-items:flex-start">
                    <div style="width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;
                                {{ $color === 'blue' ? 'background:#EFF4FB;color:#1B2A8A' : ($color === 'teal' ? 'background:#ECFDF5;color:#1D9E75' : 'background:#FFFBEB;color:#D97706') }}">
                        <svg style="width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>
                        </svg>
                    </div>
                    <div>
                        <p style="font-size:13px;font-weight:800;color:#0F172A;margin:0 0 4px">{!! $title !!}</p>
                        <p style="font-size:12px;color:#64748b;line-height:1.6;margin:0">{{ $desc }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
</div>

{{-- ═══════════════════════════════════════════════════════
     FEATURES
═══════════════════════════════════════════════════════ --}}
<div id="fitur" style="background:#f8fafc;padding:0">
<div class="section-wrap">
    <div style="text-align:center" class="reveal">
        <div class="section-badge" style="margin:0 auto 14px">
            <span>Main Features</span>
        </div>
        <h2 style="font-size:30px;font-weight:900;color:#0F172A;margin:0 0 12px">Designed for Ease of Use</h2>
        <p style="font-size:14px;color:#64748b;max-width:480px;margin:0 auto">A system that is easy to use for all levels — from employees to administrators.</p>
    </div>
    <div class="feat-grid reveal" style="transition-delay:.12s">
        @foreach([
            ['#EFF4FB','#1B2A8A','M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3',
             'No Login Required for Employees',
             'Employees can view the dashboard directly without needing an account.'],
            ['#ECFDF5','#1D9E75','M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5',
             'Automatic Excel Upload',
             'Upload Draft_Daily.xlsx and data automatically enters the database and updates the dashboard.'],
            // ['#FFFBEB','#D97706','M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3',
            //'Donut Chart per Site',
            //'Visualisasi FC MTD by Product untuk tiap site dengan breakdown sub-site dan achievement %.'],
            ['#FDF2F8','#9333EA','M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z',
             'Filament Admin Panel',
             'Complete admin panel for superadmins and operators — manage users, data, and upload Excel files.'],
        ] as [$bg,$color,$path,$title,$desc])
        <div class="feat-card">
            <div class="feat-ico" style="background:{{ $bg }};color:{{ $color }}">
                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
            </div>
            <p style="font-size:13px;font-weight:800;color:#0F172A;margin:0 0 6px">{{ $title }}</p>
            <p style="font-size:11px;color:#64748b;line-height:1.6;margin:0">{{ $desc }}</p>
        </div>
        @endforeach
    </div> 
</div>
</div>

{{-- ═══════════════════════════════════════════════════════
     FOOTER
═══════════════════════════════════════════════════════ --}}
<footer class="footer">
    <div class="footer-inner">
        <div class="footer-grid">
            <div>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px">
                    <div style="width:64px;height:64px;border-radius:10px;
                                display:flex;align-items:center;justify-content:center">
                                <img src="{{ asset('img/ITM_Logo_3.png') }}" alt="ITM Logo" style="width:100%;height:100%;object-fit:contain;border-radius:12px;margin-bottom:10px;padding:6px;">
                    </div>
                    <p style="font-size:14px;font-weight:900;color:white;margin:0">ITM Production Dashboard</p>
                </div>
                <p style="font-size:12px;color:rgba(255,255,255,.45);line-height:1.7;margin:0;max-width:280px">
                    System monitoring production of coal PT Indo Tambangraya Megah Tbk. Accurate data and easy to access.
                </p>
            </div>
            <div>
                <p class="footer-lbl">Dashboard</p>
                <a href="{{ route('public.daily') }}" class="footer-link">Daily Dashboard</a>
                <a href="{{ route('public.third-party') }}" class="footer-link">3rd Party Dashboard</a>
                <a href="{{ route('public.loading') }}" class="footer-link">Weekly Dashboard</a>
                <a href="{{ route('public.poa') }}" class="footer-link">POA Dashboard</a>
            </div>
            <div>
                <p class="footer-lbl">System</p>
                <a href="/admin" class="footer-link">Admin Panel</a>
                <a href="/admin" class="footer-link">Login Operator</a>
            </div>
        </div>
        <hr class="footer-divider">
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} CBIC. All rights reserved.</p>
            <p>Production Dashboard System v1.0</p>
        </div>
    </div>
</footer>

<script>
/* ── Scroll reveal ─────────────────────────────────────────── */
const observer = new IntersectionObserver(entries => {
    entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('visible'); });
}, { threshold: 0.15 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

/* ── Counter animation ─────────────────────────────────────── */
const counters = document.querySelectorAll('.js-count');
const statsObs = new IntersectionObserver(entries => {
    entries.forEach(e => {
        if(!e.isIntersecting) return;
        counters.forEach(el => {
            const target = parseInt(el.dataset.target, 10);
            const start  = performance.now();
            const step   = ts => {
                const p = Math.min((ts - start) / 1400, 1);
                const ease = 1 - Math.pow(1-p, 3);
                el.textContent = Math.round(target * ease).toLocaleString('id-ID');
                if(p < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        });
        statsObs.disconnect();
    });
}, { threshold: 0.5 });
if(counters.length) statsObs.observe(document.querySelector('.stats-bar'));

/* ── Bar chart animation ───────────────────────────────────── */
const barObs = new IntersectionObserver(entries => {
    entries.forEach(e => {
        if(!e.isIntersecting) return;
        e.target.querySelectorAll('.about-bar-fill').forEach(bar => {
            const pct = bar.dataset.width;
            setTimeout(() => bar.style.width = pct + '%', 200);
        });
    });
}, { threshold: 0.4 });
document.querySelectorAll('.about-chart').forEach(el => barObs.observe(el));
</script>
</body>
</html>