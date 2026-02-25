{{-- Print Footer --}}
<div class="print-footer" style="display: none;">
    <table style="width: 100%; margin-top: 30px;">
        <tr>
            <td style="width: 50%; vertical-align: top; font-size: 11px; color: #666;">
                Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIT
            </td>
            <td style="width: 50%; text-align: right; vertical-align: top;">
                <div style="font-size: 12px;">
                    {{ $pengaturan->alamat_sekolah ? explode(',', $pengaturan->alamat_sekolah)[0] . ',' : '' }}
                    {{ now()->translatedFormat('d F Y') }}
                </div>
                <div style="font-size: 12px; margin-top: 5px;">{{ $pengaturan->nama_kepala_sekolah ? 'Kepala Perpustakaan' : 'Petugas' }}</div>
                <div style="height: 60px;"></div>
                <div style="font-size: 12px; font-weight: bold; border-top: 1px solid #333; display: inline-block; padding-top: 5px;">
                    {{ $pengaturan->nama_kepala_sekolah ?? Auth::user()->name }}
                </div>
            </td>
        </tr>
    </table>
</div>
