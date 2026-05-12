<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — ITM Dashboard</title>
    @vite(['resources/css/app.css'])
</head>
<body style="min-height:100vh;display:flex;align-items:center;justify-content:center;background-image:linear-gradient(160deg,rgba(27,42,138,0.15) 0%,rgba(40,81,163,0.45) 50%,rgba(239,244,251,0.75) 100%),url({{ asset('img/bg.png') }});background-size:cover;background-position:center;background-attachment:fixed">
<div style="width:100%;max-width:420px;padding:16px">

    {{-- Logo --}}
    <div style="text-align:center;margin-bottom:24px;margin-left:auto;margin-right:auto">
        <div style="width:px;height:60px;
                    border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
                    <img src="{{ asset('img/ITM_Logo_1.png') }}" alt="ITM Logo" style="width:100%;height:100%;object-fit:contain;border-radius:12px;margin-bottom:10px;padding:6px;">
            <!-- <svg style="width:28px;height:28px;stroke:white;fill:none;stroke-width:1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5"/>
            </svg> -->
        </div>
        <h1 style="font-size:22px;font-weight:900;color:white;font-family:Inter,sans-serif;margin:0">ITM Production Dashboard System</h1>
        <p style="font-size:12px;color:rgba(255,255,255,.6);margin:4px 0 0;font-family:Inter,sans-serif">
            
        </p>
    </div>

    {{-- Card --}}
    <div style="background:white;border-radius:24px;padding:32px;box-shadow:0 20px 60px rgba(0,0,0,.2)">
        <h2 style="text-align:center;font-size:16px;font-weight:800;color:#1B2A8A;margin:0 0 4px;font-family:Inter,sans-serif">
            Masuk ke Akun Anda
        </h2>
        <p style="text-align:center;font-size:11px;color:#94a3b8;margin:0 0 20px;font-family:Inter,sans-serif">
            Gunakan email dan password ITM Anda
        </p>

        @if($errors->any())
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:12px;margin-bottom:16px">
                @foreach($errors->all() as $e)
                    <p style="font-size:12px;color:#dc2626;margin:0;font-family:Inter,sans-serif">{{ $e }}</p>
                @endforeach
            </div>
        @endif

        {{-- Pesan dari /admin-access (force re-login) --}}
        @if(session('admin_required'))
        <div style="background:#EFF4FB;border:1px solid #BFDBFE;border-radius:12px;
                    padding:12px 14px;margin-bottom:16px;
                    display:flex;align-items:flex-start;gap:10px">
            <svg style="width:16px;height:16px;stroke:#1B2A8A;fill:none;stroke-width:2;flex-shrink:0;margin-top:1px" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
            </svg>
            <div>
                <p style="font-size:11px;font-weight:700;color:#1B2A8A;margin:0 0 2px">Verifikasi Diperlukan</p>
                <p style="font-size:11px;color:#475569;margin:0">{{ session('admin_required') }}</p>
            </div>
        </div>
        @endif

        {{-- Pesan error akses ditolak --}}
        @if(session('error'))
        <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:12px;
                    padding:12px 14px;margin-bottom:16px;
                    display:flex;align-items:flex-start;gap:10px">
            <svg style="width:16px;height:16px;stroke:#DC2626;fill:none;stroke-width:2;flex-shrink:0;margin-top:1px" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
            </svg>
            <p style="font-size:11px;color:#DC2626;margin:0;font-weight:600">{{ session('error') }}</p>
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div style="margin-bottom:16px">
                <label style="display:block;font-size:10px;font-weight:700;color:#64748b;
                              text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;
                              font-family:Inter,sans-serif">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="login-inp" placeholder="nama@itm.co.id"
                       style="font-family:Inter,sans-serif">
            </div>
            <div style="margin-bottom:16px">
                <label style="display:block;font-size:10px;font-weight:700;color:#64748b;
                              text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;
                              font-family:Inter,sans-serif">Password</label>
                <input type="password" name="password" required class="login-inp"
                       placeholder="••••••••" style="font-family:Inter,sans-serif">
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;
                              font-size:11px;color:#64748b;font-family:Inter,sans-serif">
                    <input type="checkbox" name="remember"> Ingat saya
                </label>
            </div>
            <button type="submit" class="login-btn" style="font-family:Inter,sans-serif">
                Masuk
            </button>
        </form>

        {{-- Role info --}}
        <!-- <div style="margin-top:20px;padding-top:16px;border-top:1px solid #f1f5f9">
            <p style="font-size:9px;text-align:center;color:#94a3b8;font-family:Inter,sans-serif;
                      margin:0 0 10px;text-transform:uppercase;letter-spacing:.06em">Akun Default</p>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;text-align:center">
                @foreach([['superadmin@itm.co.id','Super Admin','#fee2e2','#dc2626'],['operator@itm.co.id','Operator','#dbeafe','#2563eb'],['karyawan@itm.co.id','Karyawan','#f1f5f9','#64748b']] as [$email,$role,$bg,$color])
                <div style="background:{{ $bg }};border-radius:10px;padding:10px 6px">
                    <p style="font-size:9px;font-weight:800;color:{{ $color }};margin:0;font-family:Inter,sans-serif">{{ $role }}</p>
                    <p style="font-size:8px;color:#94a3b8;margin:2px 0 0;font-family:Inter,sans-serif">password</p>
                </div>
                @endforeach
            </div>
        </div> -->
    </div>

    <p style="text-align:center;font-size:10px;color:rgba(255,255,255,.3);margin-top:16px;font-family:Inter,sans-serif">
        &copy; {{ date('Y') }} CBIC - All rights reserved
    </p>
</div>
</body>
</html>
