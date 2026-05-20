<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Laporan')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        .page { padding: 15px 25px; }

        /* Header */
        .header { border-bottom: 3px double #333; padding-bottom: 12px; margin-bottom: 15px; }
        .header table { width: 100%; }
        .header .logo { width: 65px; vertical-align: middle; padding-right: 10px; }
        .header .logo img { width: 60px; height: 60px; object-fit: contain; }
        .header .info { text-align: center; vertical-align: middle; padding: 0 10px; }
        .header .school-name { font-size: 16px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .header .school-address { font-size: 9px; color: #555; margin-top: 3px; }
        .header .school-contact { font-size: 9px; color: #555; margin-top: 2px; }

        /* Report Title */
        .report-title { text-align: center; margin-bottom: 15px; }
        .report-title h2 { font-size: 14px; font-weight: bold; text-transform: uppercase; margin-bottom: 3px; }
        .report-title .period { font-size: 10px; color: #666; }

        /* Stats */
        .stats { margin-bottom: 12px; }
        .stats table { width: 100%; border-collapse: collapse; }
        .stats td { padding: 6px 10px; }
        .stat-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 8px 12px; text-align: center; }
        .stat-label { font-size: 9px; color: #666; text-transform: uppercase; }
        .stat-value { font-size: 14px; font-weight: bold; color: #333; margin-top: 2px; }

        /* Data Table */
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .data-table th {
            background-color: #f0f0f0;
            border: 1px solid #999;
            padding: 6px 8px;
            font-size: 10px;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
            color: #444;
        }
        .data-table td {
            border: 1px solid #ccc;
            padding: 5px 8px;
            font-size: 10px;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) { background-color: #fafafa; }
        .data-table .text-center { text-align: center; }
        .data-table .text-right { text-align: right; }
        .data-table .font-bold { font-weight: bold; }
        .data-table .text-red { color: #dc2626; }
        .data-table .text-green { color: #059669; }

        /* Footer Table */
        .data-table tfoot td {
            background-color: #f0f0f0;
            font-weight: bold;
            border: 1px solid #999;
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-blue { background: #dbeafe; color: #1d4ed8; }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .badge-purple { background: #ede9fe; color: #5b21b6; }
        .badge-gray { background: #f3f4f6; color: #374151; }

        /* Footer */
        .footer { margin-top: 25px; }
        .footer table { width: 100%; }
        .footer .print-date { font-size: 9px; color: #888; vertical-align: bottom; }
        .footer .sign-area { text-align: right; }
        .footer .sign-place { font-size: 10px; }
        .footer .sign-title { font-size: 10px; margin-top: 3px; }
        .footer .sign-space { height: 55px; }
        .footer .sign-name { font-size: 10px; font-weight: bold; border-top: 1px solid #333; display: inline-block; padding-top: 3px; }

        /* Empty State */
        .empty-state { text-align: center; padding: 40px 20px; color: #888; font-size: 12px; }

        @yield('extra-styles')
    </style>
</head>
<body>
    <div class="page">
        {{-- Header --}}
        @php
            $logoSrc = null;
            if ($pengaturan && $pengaturan->logo) {
                $logoPath = public_path($pengaturan->logo);
                if (file_exists($logoPath)) {
                    $ext  = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
                    $mime = in_array($ext, ['jpg','jpeg']) ? 'image/jpeg' : 'image/png';
                    $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
                }
            }
        @endphp
        <div class="header">
            <table>
                <tr>
                    <td class="logo">
                        @if($logoSrc)
                            <img src="{{ $logoSrc }}" alt="Logo">
                        @endif
                    </td>
                    <td class="info">
                        
                        @if($pengaturan && $pengaturan->deskripsi_website)
                            <div class="school-name"">
                                {{ $pengaturan->deskripsi_website }}
                            </div>
                        @endif
                        @if($pengaturan && $pengaturan->alamat_sekolah)
                            <div class="school-address">{{ $pengaturan->alamat_sekolah }}</div>
                        @endif
                        @if($pengaturan && ($pengaturan->telepon_sekolah || $pengaturan->email_sekolah))
                            <div class="school-contact">
                                @if($pengaturan->telepon_sekolah)Telp: {{ $pengaturan->telepon_sekolah }}@endif
                                @if($pengaturan->telepon_sekolah && $pengaturan->email_sekolah) | @endif
                                @if($pengaturan->email_sekolah)Email: {{ $pengaturan->email_sekolah }}@endif
                            </div>
                        @endif
                    </td>
                    <td style="width: 65px;"></td>
                </tr>
            </table>
        </div>

        {{-- Report Title --}}
        <div class="report-title">
            <h2>@yield('report-title', 'LAPORAN')</h2>
            @if(request('tanggal_mulai') && request('tanggal_akhir'))
                <div class="period">
                    Periode: {{ \Carbon\Carbon::parse(request('tanggal_mulai'))->format('d/m/Y') }} - {{ \Carbon\Carbon::parse(request('tanggal_akhir'))->format('d/m/Y') }}
                </div>
            @endif
        </div>

        {{-- Stats --}}
        @yield('stats')

        {{-- Content --}}
        @yield('content')

        {{-- Footer --}}
        <div class="footer">
            <table>
                <tr>
                    <td class="print-date" style="width: 50%; vertical-align: bottom;">
                        Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIT
                    </td>
                    <td class="sign-area" style="width: 50%;">
                        <div class="sign-place">
                            @if($pengaturan && $pengaturan->alamat_sekolah)
                                {{ explode(',', $pengaturan->alamat_sekolah)[0] }},
                            @endif
                            {{ now()->translatedFormat('d F Y') }}
                        </div>
                        <div class="sign-title">{{ $pengaturan->nama_kepala_sekolah ? 'Kepala Perpustakaan' : 'Petugas' }}</div>
                        <div class="sign-space"></div>
                        <div class="sign-name">{{ $pengaturan->nama_kepala_sekolah ?? Auth::user()->name }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
