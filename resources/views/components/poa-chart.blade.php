{{--
    Props:
    - $company   : string (IMM, TCM, dll)
    - $chartData : array (monthly_data dari controller)
    - $yMax      : int
    - $index     : int (untuk animasi delay)
    - $snapshotId : int (opsional, untuk admin edit)
    - $year      : int (opsional, untuk admin edit)
--}}
@props(['company', 'chartData', 'yMax', 'index' => 0, 'snapshotId' => null, 'year' => null])

<div class="poa-chart-card"
     style="animation-delay: {{ $index * 80 }}ms"
     data-company="{{ $company }}"
     data-snapshot-id="{{ $snapshotId }}">

    {{-- Company header dengan placeholder untuk logo --}}
    <div class="poa-card-header">
        {{-- Logo image — ganti src dengan logo asli --}}
        <img src="{{ asset('img/' . strtolower($company) . '.png') }}"
             alt="{{ $company }}"
             class="company-logo"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        {{-- Fallback teks jika logo belum ada --}}
        <div class="company-logo-fallback" style="display:none">
            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21"/>
            </svg>
            <span>{{ $company }}</span>
        </div>
    </div>

    {{-- Chart container --}}
    <div class="poa-chart-wrap">
        <canvas id="chart-{{ $company }}-{{ $index }}"
                class="poa-canvas"
                data-chart='@json($chartData)'
                data-ymax="{{ $yMax }}"
                data-company="{{ $company }}"
                data-snapshot-id="{{ $snapshotId }}"
                data-year="{{ $year }}">
        </canvas>
    </div>

    {{-- Legend dengan asterisk button (hanya untuk admin) --}}
    @if($snapshotId && auth()->user()?->isSuperAdmin() && !auth()->user()?->isOperator())
    <div style="padding:8px 12px;border-top:1px solid #e2e8f0;font-size:10px;color:#64748b">
        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
            <span style="font-weight:600">Months with (*): </span>
            @php
                $provisionalMonths = collect($chartData)
                    ->filter(fn($m) => $m['is_provisional'])
                    ->pluck('month_name')
                    ->join(', ');
            @endphp
            <span style="color:#475569">
                {{ $provisionalMonths ?: 'None' }}
            </span>
        </div>
        <div style="margin-top:6px;font-size:9px;color:#94a3b8">
            Click chart months to toggle (*)
        </div>
    </div>
    @endif
</div>

