<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Setoran;
use App\Models\KategoriSampah;
use Pdf;
use App\Models\DetailSetoran;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon; // Tambahkan ini untuk format tanggal grafik

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Total Nasabah
        $totalNasabah = User::where('role', 'nasabah')->count();

        // 2. Total Sampah Terkumpul
        $totalSampah = Setoran::sum('total_berat');

        // 3. Total Tabungan Nasabah
        $totalTabungan = User::where('role', 'nasabah')->sum('saldo');

        // 4. Cari Jenis Sampah Populer (Diselaraskan dengan total BERAT terbesar)
        $sampahPopulerData = DetailSetoran::select('kategori_id', DB::raw('SUM(berat) as total_berat'))
            ->groupBy('kategori_id')
            ->orderByDesc('total_berat')
            ->first();

        $kategoriPopuler = $sampahPopulerData ? $sampahPopulerData->kategori->nama_jenis : 'Belum Ada';

        // =========================================================================
        // 🔥 FITUR UTAMA: LOGIKA HITUNG PERSENTASE BERDASARKAN NAMA_JENIS (BAHASA INDONESIA)
        // =========================================================================
        // Ambil akumulasi berat dari database, langsung dikelompokkan per nama_jenis
        $chartPieRaw = DetailSetoran::join('kategori_sampahs', 'detail_setorans.kategori_id', '=', 'kategori_sampahs.id')
            ->select('kategori_sampahs.nama_jenis', DB::raw('SUM(detail_setorans.berat) as total_berat'))
            ->groupBy('kategori_sampahs.nama_jenis')
            ->orderByDesc('total_berat')
            ->get();

        // Inisialisasi array kosong di awal agar aman dari error undefined variabel jika DB kosong
        $pieLabels = [];
        $pieDataPersen = [];
        $pieDataBerat = [];

        $totalBeratAll = $chartPieRaw->sum('total_berat') > 0 ? $chartPieRaw->sum('total_berat') : 1; 

        // Loop langsung dari koleksi objek hasil query database
        foreach ($chartPieRaw as $row) {
            $persen = ($row->total_berat / $totalBeratAll) * 100;
            
            $pieLabels[] = $row->nama_jenis; 
            $pieDataPersen[] = round($persen, 1);  
            $pieDataBerat[] = round($row->total_berat, 1); 
        }
        // =========================================================================

        // 5. Data untuk Grafik Chart.js (Setoran 7 Hari Terakhir - Line/Bar Chart)
        $grafikSetoran = Setoran::select(
                DB::raw('DATE(created_at) as tanggal'),
                DB::raw('SUM(total_berat) as total_berat')
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'ASC')
            ->get();

        // Inisialisasi array kosong untuk grafik batang mingguan
        $chartLabels = [];
        $chartData = [];

        // Jika ada data, baru array di atas diisi
        foreach ($grafikSetoran as $row) {
            $chartLabels[] = Carbon::parse($row->tanggal)->format('d M');
            $chartData[] = $row->total_berat;
        }

        // Kirim semua variabel ke dalam View dashboard.blade.php
        return view('dashboard', compact(
            'totalNasabah', 
            'totalSampah', 
            'totalTabungan', 
            'kategoriPopuler',
            'chartLabels',   
            'chartData',    
            'pieLabels',   
            'pieDataPersen',
            'pieDataBerat'
        ));
    }

    // menampilkan detail dashboard dari nasabah,jumlah sampah,nominal,total_penarikan,saldo
    public function detail($tipe)
    {
        // Ambil data berdasarkan tipe untuk ditampilkan di halaman detail
        // Contoh jika ingin menampilkan list data terbaru di dalam page detail:
        $dataList = collect();
        $title = '';

        if ($tipe === 'nasabah') {
            $title = 'Detail Data Nasabah';
        } elseif ($tipe === 'sampah') {
            $title = 'Detail Akumulasi Sampah';
        } elseif ($tipe === 'tabungan') {
            $title = 'Detail Neraca Tabungan';
        } else {
            abort(404); // Jika tipe tidak dikenal, lemparkan ke halaman error 404
        }

        // Semua kondisi mengambil data Master Summary Agregasi yang sama
        $dataList = User::where('role', 'nasabah')
        ->withSum(['setorans as total_berat_sampah'], 'total_berat') // Total seluruh Kg per orang
        ->withSum(['setorans as total_pemasukan'], 'total_harga')     // Total omset Rupiah kotor per orang
        ->orderBy('name', 'asc')
        ->get();

        return view('detail', compact('tipe', 'title', 'dataList'));
    }
    // Menampilkan halaman form ganti password admin
    public function gantiPassword()
    {
        return view('ganti-password'); // Pastikan ini mengarah ke folder views/admin
    }

    // Memproses perubahan password admin
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_sekarang' => 'required',
            'password_baru'     => 'required|min:8|confirmed',
        ], [
            'password_sekarang.required' => 'Password saat ini wajib diisi.',
            'password_baru.required'     => 'Password baru wajib diisi.',
            'password_baru.min'          => 'Password baru minimal 8 karakter.',
            'password_baru.confirmed'    => 'Konfirmasi password baru tidak cocok.',
        ]);

        $admin = Auth::user();

        if (!Hash::check($request->password_sekarang, $admin->password)) {
            return back()->with('error', 'Password saat ini salah.');
        }

        $admin->update([
            'password' => Hash::make($request->password_baru)
        ]);

        return back()->with('success', 'Password Admin berhasil diperbarui!');
    }
    public function downloadLaporan()
    {
        // 1. Ambil data transaksi bulan berjalan yang sudah sukses terverifikasi
        $bulanIni = Carbon::now()->month;
        $tahunIni = Carbon::now()->year;

        $setoranSukses = Setoran::with(['user', 'details.kategori'])
            ->where('status', 'success')
            ->whereMonth('created_at', $bulanIni)
            ->whereYear('created_at', $tahunIni)
            ->get();

        // 2. Hitung total ringkasan untuk performa dashboard laporan
        $totalVolume = $setoranSukses->sum('total_berat');
        $totalUang = $setoranSukses->sum('total_harga');
        
        // 3. Ambil data master kategori untuk tabel rincian manifes
        $kategori = KategoriSampah::all();

        // 4. Bungkus data ke dalam array untuk dikirim ke template cetak
        $data = [
            'laporan_bulan' => Carbon::now()->translatedFormat('F Y'),
            'tanggal_cetak' => Carbon::now()->translatedFormat('d F Y'),
            'setoran'       => $setoranSukses,
            'total_volume'  => $totalVolume,
            'total_uang'    => $totalUang,
            'kategori'      => $kategori
        ];

        // 5. Load view khusus cetak cetakan pdf dan set ukuran kertas ke A4
        $pdf = Pdf::loadView('cetak_laporan', $data)->setPaper('a4', 'portrait');
        
        // 6. Alirkan file ke browser untuk otomatis terunduh
        return $pdf->download('Laporan_Bulanan_Bank_Sampah_' . Carbon::now()->format('Y_m') . '.pdf');
    }
}