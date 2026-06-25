<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Bulanan Bank Sampah</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            font-size: 11pt;
            line-height: 1.4;
        }
        .header {
            border-bottom: 3px solid #2e7d32;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 18pt;
            font-weight: bold;
            color: #1b5e20;
            text-transform: uppercase;
            margin: 0;
        }
        .subtitle {
            font-size: 10pt;
            color: #555;
            margin: 3px 0 0 0;
        }
        .meta-info {
            font-size: 9pt;
            color: #666;
            margin-top: 8px;
        }
        .stats-table {
            width: 100%;
            margin-bottom: 25px;
        }
        .stats-box {
            background-color: #f5f5f5;
            border-left: 4px solid #2e7d32;
            padding: 10px;
            width: 48%;
        }
        .stats-label {
            font-size: 8pt;
            text-transform: uppercase;
            color: #666;
            font-weight: bold;
        }
        .stats-value {
            font-size: 14pt;
            font-weight: bold;
            color: #111;
            margin-top: 3px;
        }
        h3 {
            font-size: 12pt;
            color: #1565c0;
            border-left: 3px solid #1565c0;
            padding-left: 6px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table-data th {
            background-color: #333;
            color: #fff;
            font-weight: bold;
            padding: 7px;
            font-size: 9.5pt;
            border: 1px solid #444;
            text-align: center;
        }
        .table-data td {
            padding: 7px;
            border: 1px solid #ddd;
            font-size: 9.5pt;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .signature-container {
            margin-top: 40px;
            width: 100%;
        }
        .signature-box {
            float: right;
            width: 200px;
            text-align: center;
            font-size: 10pt;
        }
        .signature-space {
            height: 60px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="title">Laporan Rekapitulasi Setoran Bulanan</div>
        <div class="subtitle">Sistem Informasi Bank Sampah Informatika Berbasis AI (MobileNetV2)</div>
        <div class="meta-info">
            Periode Laporan: <strong>{{ $laporan_bulan }}</strong> | Tanggal Cetak: {{ $tanggal_cetak }} | Oleh: Operator Loket
        </div>
    </div>

    <table class="stats-table">
        <tr>
            <td class="stats-box">
                <div class="stats-label">Total Volume Tonase Sampah</div>
                <div class="stats-value">{{ number_format($total_volume, 2, ',', '.') }} Kg</div>
            </td>
            <td style="width: 4%;"></td>
            <td class="stats-box" style="border-left-color: #1565c0;">
                <div class="stats-label">Total Alokasi Saldo Terdistribusi</div>
                <div class="stats-value">Rp {{ number_format($total_uang, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <h3>1. Log Manifes Jurnal Transaksi Sukses Terverifikasi</h3>
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 15%;">Kode TRX</th>
                <th style="width: 25%;">Waktu Validasi</th>
                <th style="width: 25%;">Nama Nasabah</th>
                <th style="width: 15%;">Berat Total</th>
                <th style="width: 20%;">Total Pembayaran</th>
            </tr>
        </thead>
        <tbody>
            @forelse($setoran as $s)
                <tr>
                    <td class="text-center fw-bold">{{ $s->kode_transaksi }}</td>
                    <td class="text-center">{{ $s->created_at->format('d M Y, H:i') }}</td>
                    <td>{{ $s->user->name }}</td>
                    <td class="text-center">{{ number_format($s->total_berat, 2, ',', '.') }} Kg</td>
                    <td class="text-right">Rp {{ number_format($s->total_harga, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #666; font-style: italic; padding: 20px;">Belum ada transaksi sukses yang divalidasi pada bulan ini.</td>
                </tr>
            @endforelse
        </tbody>
        @if($setoran->count() > 0)
            <tfoot>
                <tr style="background-color: #eaeaea; font-weight: bold;">
                    <td colspan="3" class="text-center">AKUMULASI TOTAL</td>
                    <td class="text-center">{{ number_format($total_volume, 2, ',', '.') }} Kg</td>
                    <td class="text-right">Rp {{ number_format($total_uang, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <p style="font-size: 8.5pt; color: #555; text-align: justify; margin-top: 15px;">
        * Dokumen ini sah dan diterbitkan secara otomatis oleh sistem backend transaksi Bank Sampah Digital menggunakan enkripsi relasi basis data Laravel Eloquent yang telah disetujui melalui verifikasi timbangan fisik loket.
    </p>

    <div class="signature-container">
        <div class="signature-box">
            <p>Depok, {{ $tanggal_cetak }}</p>
            <p class="fw-bold" style="margin-top: -5px;">Petugas Loket Utama</p>
            <div class="signature-space"></div>
            <hr style="border: 0; border-top: 1px solid #333;">
            <p style="font-size: 9pt; color: #333; margin-top: 2px;">Sistem Informasi Sampah</p>
        </div>
    </div>

</body>
</html>