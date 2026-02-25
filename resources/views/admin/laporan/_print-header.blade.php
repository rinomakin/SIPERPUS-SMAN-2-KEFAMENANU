{{-- Print Header - Dynamic School Info --}}
<div class="print-header" style="display: none;">
    <table style="width: 100%; border-bottom: 3px double #333; padding-bottom: 15px; margin-bottom: 20px;">
        <tr>
            <td style="width: 80px; vertical-align: middle;">
                @if($pengaturan && $pengaturan->logo)
                    <img src="{{ asset('storage/' . $pengaturan->logo) }}" alt="Logo" style="width: 70px; height: 70px; object-fit: contain;">
                @else
                    <div style="width: 70px; height: 70px; background: #e5e7eb; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 24px; font-weight: bold; color: #6b7280;">P</span>
                    </div>
                @endif
            </td>
            <td style="text-align: center; vertical-align: middle; padding: 0 10px;">
                <div style="font-size: 18px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">
                    {{ $pengaturan->nama_website ?? 'PERPUSTAKAAN' }}
                </div>
                @if($pengaturan && $pengaturan->alamat_sekolah)
                    <div style="font-size: 11px; color: #555; margin-top: 4px;">
                        {{ $pengaturan->alamat_sekolah }}
                    </div>
                @endif
                @if($pengaturan && ($pengaturan->telepon_sekolah || $pengaturan->email_sekolah))
                    <div style="font-size: 11px; color: #555; margin-top: 2px;">
                        @if($pengaturan->telepon_sekolah)Telp: {{ $pengaturan->telepon_sekolah }}@endif
                        @if($pengaturan->telepon_sekolah && $pengaturan->email_sekolah) | @endif
                        @if($pengaturan->email_sekolah)Email: {{ $pengaturan->email_sekolah }}@endif
                    </div>
                @endif
            </td>
            <td style="width: 80px;"></td>
        </tr>
    </table>
    <div style="text-align: center; margin-bottom: 15px;">
        <h2 style="font-size: 16px; font-weight: bold; margin: 0;">{{ $judulLaporan ?? 'LAPORAN' }}</h2>
        @if(request('tanggal_mulai') && request('tanggal_akhir'))
            <p style="font-size: 12px; color: #555; margin: 5px 0 0 0;">
                Periode: {{ \Carbon\Carbon::parse(request('tanggal_mulai'))->format('d/m/Y') }} - {{ \Carbon\Carbon::parse(request('tanggal_akhir'))->format('d/m/Y') }}
            </p>
        @endif
    </div>
</div>
