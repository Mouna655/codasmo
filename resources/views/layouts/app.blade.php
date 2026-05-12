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
    <style>
        .sidebar-logo-area { display:flex; align-items:center; gap:10px;}
        .sidebar-logo-icon{
            width:64px; height:64px;
            border-radius:10px; display:flex; align-items:center; justify-content:center;
            background:transparent
        }
    </style>
</head>
<body class="h-full bg-itm-bg font-sans antialiased" x-data="{ sidebarOpen: true, sidebarMobile: false }">

{{-- ── SIDEBAR ──────────────────────────────────────────────── --}}
<aside
    class="sidebar-wrap"
    :class="{ 'sidebar-collapsed': !sidebarOpen, 'sidebar-mobile-open': sidebarMobile }">

    {{-- Logo --}}
    <div class="sidebar-logo-area ">
        <div class="sidebar-logo-icon">
            <img src="{{ asset('img/ITM_Logo_1.png') }}" alt="ITM Logo" style="width:100%;height:100%;object-fit:contain;border-radius:12px;margin-bottom:10px;padding:6px;">
        </div>
        <div class="sidebar-logo-text" x-show="sidebarOpen" x-transition:enter="transition-opacity duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <p class="text-sm font-extrabold text-blue-900 tracking-tight leading-none">ITM</p>
            <p class="text-[10px] text-blue-900 font-medium mt-0.5">Production Dashboard</p>
        </div>
    </div>

    {{-- Nav --}}
    <nav class="sidebar-nav">
        <p class="sidebar-section-label" x-show="sidebarOpen">Menu Utama</p>

        <a href="{{ route('dashboard') }}"
           class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
           title="Daily Dashboard">
            <span class="sidebar-item-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                </svg>
            </span>
            <span class="sidebar-item-label" x-show="sidebarOpen">Daily Dashboard</span>
            @if(request()->routeIs('dashboard'))
                <span class="sidebar-active-dot" x-show="!sidebarOpen"></span>
            @endif
        </a>

        <a href="{{ route('dashboard.third-party') }}"
           class="sidebar-item my-2 {{ request()->routeIs('dashboard.third-party') ? '' : '' }}"
           title="3rd Party Dashboard">
            <span class="sidebar-item-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                    <path fill="currentColor" 
                    d="M6 20c1.1 0 2-.9 2-2v-7c0-1.1-.9-2-2-2s-2 .9-2 2v7c0 1.1.9 2 2 2m10-5v3c0 1.1.9 2 2 2s2-.9 2-2v-3c0-1.1-.9-2-2-2s-2 .9-2 2m-4 5c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2s-2 .9-2 2v12c0 1.1.9 2 2 2"/>
                </svg>
            </span>
            <span class="sidebar-item-label" x-show="sidebarOpen">3rd Party Dashboard</span>
            @if(request()->routeIs('dashboard.third-party'))
                <span class="sidebar-active-dot" x-show="!sidebarOpen"></span>
            @endif
        </a>

        <a href="{{ route('dashboard.loading') }}"
           class="sidebar-item my-2 {{ request()->routeIs('dashboard.loading') ? '' : '' }}"
           title="Loading Dashboard">
            <span class="sidebar-item-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                    <path fill="currentColor" 
                    d="M8.85 15H12q.425 0 .713-.288T13 14t-.288-.712T12 13H8.85l.875-.9q.275-.275.275-.687t-.3-.713q-.275-.275-.7-.275t-.7.275l-2.6 2.6q-.3.3-.3.7t.3.7l2.6 2.6q.275.275.688.288T9.7 17.3q.275-.275.288-.687t-.263-.713zm6.3-4l-.875.9q-.275.275-.275.688t.3.712q.275.275.7.275t.7-.275l2.6-2.6q.3-.3.3-.7t-.3-.7l-2.6-2.6q-.275-.275-.687-.288T14.3 6.7q-.275.275-.288.688t.263.712l.875.9H12q-.425 0-.712.288T11 10t.288.713T12 11zM12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22m0-2q3.35 0 5.675-2.325T20 12t-2.325-5.675T12 4T6.325 6.325T4 12t2.325 5.675T12 20m0-8"/>
            </svg>
            </span>
            <span class="sidebar-item-label" x-show="sidebarOpen">Loading Dashboard</span>
            @if(request()->routeIs('dashboard.loading'))
                <span class="sidebar-active-dot" x-show="!sidebarOpen"></span>
            @endif
        </a>

        <a href="{{ route('dashboard.shipment') }}"
           class="sidebar-item my-2 {{ request()->routeIs('dashboard.shipment') ? '' : '' }}"
           title="Shipment Dashboard">
            <span class="sidebar-item-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                    <path fill="currentColor" 
                    d="M10.288 21.775q-.813-.2-1.813-.675q-1.175.475-2.237.65T4 21.975q-.425.025-.712-.262T3 21t.288-.712t.712-.313q.6-.025 1.113-.075t1.012-.162t1.025-.288t1.125-.425q.125-.05.25-.05t.25.05q.825.425 1.513.7T12 20t1.713-.275t1.512-.7q.125-.05.25-.05t.25.05q.6.25 1.125.425t1.025.288t1.025.162t1.1.075q.425.025.713.313T21 21t-.288.713t-.712.262q-1.175-.05-2.238-.225t-2.212-.65q-1 .475-1.825.675t-1.725.2t-1.712-.2M12 18q-1.5 0-2.625-1L8.25 16q-.475.45-1.012.813t-1.113.637q-.475.2-.937-.05t-.613-.75L2.825 11q-.125-.425.075-.775t.625-.475L5 9.35V6q0-.825.588-1.412T7 4h2.5V3q0-.425.288-.712T10.5 2h3q.425 0 .713.288T14.5 3v1H17q.825 0 1.413.588T19 6v3.35l1.475.4q.425.125.625.475t.075.775l-1.75 5.65q-.15.5-.612.75t-.938.05q-.6-.275-1.137-.638T15.75 16l-1.125 1Q13.5 18 12 18M7 6v2.825l4.5-1.2q.25-.075.5-.075t.5.075l4.5 1.2V6zm5 3.575L5.05 11.4l1.15 3.725q.375-.3.713-.613t.687-.662q.3-.325.738-.312t.712.362q.575.675 1.3 1.388t1.7.712q.95 0 1.65-.725t1.275-1.375q.275-.35.713-.363t.737.313q.35.35.688.663t.712.612l1.15-3.725zm.025 3.2"/>
            </svg>
            </span>
            <span class="sidebar-item-label" x-show="sidebarOpen">Shipment Dashboard</span>
            @if(request()->routeIs('dashboard.shipment'))
                <span class="sidebar-active-dot" x-show="!sidebarOpen"></span>
            @endif
        </a>

        <a href="{{ route('dashboard.poa') }}"
           class="sidebar-item my-2 {{ request()->routeIs('dashboard.poa') ? '' : '' }}"
           title="POA Dashboard">
            <span class="sidebar-item-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                </svg>
            </span>
            <span class="sidebar-item-label" x-show="sidebarOpen">POA Dashboard</span>
            @if(request()->routeIs('dashboard.poa'))
                <span class="sidebar-active-dot" x-show="!sidebarOpen"></span>
            @endif
        </a>

        @if(auth()->user()->canEnterData())
        <p class="sidebar-section-label" x-show="sidebarOpen">Data & Import</p>

        <a href="/admin/excel-imports"
           class="sidebar-item {{ request()->is('admin/excel-imports*') ? 'active' : '' }}"
           title="Import Excel">
            <span class="sidebar-item-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                </svg>
            </span>
            <span class="sidebar-item-label" x-show="sidebarOpen">Import Excel Daily</span>
        </a>

        <!-- <a href="/admin/excel-imports"
           class="sidebar-item {{ request()->is('admin/excel-imports*') ? 'active' : '' }}"
           title="Input Manual">
            <span class="sidebar-item-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                </svg>
            </span>
            <span class="sidebar-item-label" x-show="sidebarOpen">Input Manual</span>
        </a>
        @endif -->

        <!-- @if(auth()->user()->isSuperAdmin())
        <p class="sidebar-section-label" x-show="sidebarOpen">Administrasi</p>

        <a href="/admin/users"
           class="sidebar-item {{ request()->is('admin/users*') ? 'active' : '' }}"
           title="Kelola User">
            <span class="sidebar-item-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                </svg>
            </span>
            <span class="sidebar-item-label" x-show="sidebarOpen">Kelola User</span>
        </a>

        <a href="/admin/sites"
           class="sidebar-item {{ request()->is('admin/sites*') ? 'active' : '' }}"
           title="Master Site">
            <span class="sidebar-item-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/>
                </svg>
            </span>
            <span class="sidebar-item-label" x-show="sidebarOpen">Master Site</span>
        </a>
        @endif -->
    </nav>

    {{-- Collapse toggle --}}
    <button @click="sidebarOpen = !sidebarOpen"
            class="sidebar-collapse-btn" title="Toggle sidebar">
        <svg class="w-4 h-4 transition-transform duration-300"
             :class="sidebarOpen ? '' : 'rotate-180'"
             fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
        </svg>
    </button>

    {{-- User card --}}
    <div class="sidebar-user">
        <div class="sidebar-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
        <div class="sidebar-user-info" x-show="sidebarOpen">
            <p class="text-xs font-bold text-blue-900 truncate leading-none">{{ auth()->user()->name }}</p>
            <p class="text-[10px] text-blue-900 mt-0.5 capitalize">{{ auth()->user()->role }}</p>
        </div>
        <form method="POST" action="{{ route('logout') }}" x-show="sidebarOpen">
            @csrf
            <button type="submit" class="sidebar-logout-btn" title="Keluar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                </svg>
            </button>
        </form>
    </div>
</aside>

{{-- Mobile overlay --}}
<div class="sidebar-overlay" x-show="sidebarMobile" @click="sidebarMobile = false"
     x-transition:enter="transition-opacity duration-200"
     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity duration-200"
     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
</div>

{{-- ── MAIN CONTENT ─────────────────────────────────────────── --}}
<div class="main-wrapper" :class="sidebarOpen ? 'sidebar-expanded' : 'sidebar-collapsed-main'">

    {{-- Topbar --}}
    <header class="topbar no-print">
        <div class="flex items-center gap-3">
            {{-- Mobile hamburger --}}
            <button @click="sidebarMobile = !sidebarMobile"
                    class="lg:hidden p-2 rounded-xl hover:bg-slate-100 text-slate-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
            </button>

            <div>
                <h1 class="text-sm font-bold text-slate-800 leading-tight">@yield('page-title', 'Daily Dashboard')</h1>
                <p class="text-[10px] text-slate-400">@yield('page-subtitle', 'ITM Production System')</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            {{-- Live indicator --}}
            <div class="flex items-center gap-1.5 text-xs text-emerald-600 font-semibold">
                <span class="live-pulse-dot"></span>
                Live
            </div>
            {{-- Clock --}}
            <div class="hidden sm:block text-xs text-slate-500 bg-slate-100 rounded-xl px-3 py-1.5 font-medium tabular-nums"
                 id="live-clock"></div>
        </div>
    </header>

    {{-- Page content --}}
    <main class="page-main">
        @yield('content')
    </main>
</div>

{{-- Live clock --}}
<script>
    (function tick() {
        const el = document.getElementById('live-clock');
        if (!el) return;
        const d = new Date();
        el.textContent = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
                       + '  ' + d.toLocaleTimeString('id-ID');
        setTimeout(tick, 1000);
    })();
</script>

@stack('scripts')
</body>
</html>