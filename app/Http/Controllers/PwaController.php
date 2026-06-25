<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setoran;
use App\Models\DetailSetoran;
use App\Models\Kategori;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PwaController extends Controller
{
    public function index() {
        $userId = auth()->id();
        $totalSampah = Setoran::where('user_id', $userId)->sum('total_berat');
        $totalTabungan = auth()->user()->saldo;
        $riwayatTerbaru = Setoran::where('user_id', $userId)
                        ->latest()
                        ->limit(5)
                        ->get();
        return view('pwa.home',compact('totalSampah', 'totalTabungan','riwayatTerbaru'));
    }
    public function riwayat() {
        $transaksi = Setoran::where('user_id', auth()->id())
                ->latest()
                ->get();

        // Kirim variabel $transaksi ke view
        return view('pwa.riwayat', compact('transaksi'));
    }
    public function scan() {
        return view('pwa.scan');
    }
    // Menampilkan halaman form ganti password
    public function gantiPassword()
    {
        return view('pwa.ganti-password'); 
    }

    // Memproses perubahan password
    public function updatePassword(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'password_sekarang' => 'required',
            'password_baru'     => 'required|min:8|confirmed', // Harus sama dengan input 'password_baru_confirmation'
        ], [
            'password_sekarang.required' => 'Password saat ini wajib diisi.',
            'password_baru.required'     => 'Password baru wajib diisi.',
            'password_baru.min'          => 'Password baru minimal 8 karakter.',
            'password_baru.confirmed'    => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user = Auth::user(); // Ambil data user yang sedang login

        // 2. Cek apakah "Password Sekarang" cocok dengan yang ada di database
        if (!Hash::check($request->password_sekarang, $user->password)) {
            return back()->with('error', 'Password saat ini yang Anda masukkan salah.');
        }

        // 3. Update password baru (Jangan lupa di-Hash)
        // Gunakan model User untuk update, karena Auth::user() adalah instance dari App\Models\User
        $user->update([
            'password' => Hash::make($request->password_baru)
        ]);

        // 4. Kembalikan ke halaman dengan pesan sukses
        return back()->with('success', 'Password Anda berhasil diperbarui!');
    }
    // Fungsi 1: Kirim gambar ke Flask AI
    public function prosesScanAI(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ], [
            'image.required' => 'Gambar tidak boleh kosong.',
            'image.image'    => 'File harus berupa gambar yang valid.',
            'image.max'      => 'Ukuran gambar maksimal 5MB.',
        ]);

        $flask_url = 'http://127.0.0.1:5000/predict';

        try {
            $foto = $request->file('image');
            $response = Http::attach('image', file_get_contents($foto), $foto->getClientOriginalName())->post($flask_url);
            
            if ($response->serverError() || $response->clientError()) {
                return response()->json(['error' => 'Gagal terhubung ke Server AI Flask. Pastikan server menyala.'], 500);
            }
            
            $hasil_ai = $response->json();

            if (!isset($hasil_ai['label'])) {
                return response()->json(['error' => 'Format respons dari AI tidak sesuai standar.'], 500);
            }

            // 1. Tangkap hasil prediksi kata dari AI Flask (misal: 'trash' atau 'plastic')
            $kategori_ai = strtolower($hasil_ai['label']);
            
            // Perhitungan akurasi desimal ke persen
            $confidence_raw = $hasil_ai['confidence'] ?? 0;
            $akurasi = ($confidence_raw <= 1) ? round($confidence_raw * 100, 2) : (float) $confidence_raw;

            if ($akurasi < 50) {
                return response()->json([
                    'error' => "AI kurang yakin ({$akurasi}%). Mohon foto objek lebih dekat atau dengan pencahayaan terang."
                ], 422);
            }

            // =========================================================================
            // 🔥 LOGIKA DINAMIS: MENGAMBIL DATA LANGSUNG DARI DATABASE
            // =========================================================================
            // Kita filter kategori berdasarkan kolom label_ai hasil tebakan Flask
            $KategoriCocok = \App\Models\KategoriSampah::where('kode_kategori', $kategori_ai)->get();

            if ($KategoriCocok->isNotEmpty()) {
                // Ambil data pertama sebagai rekomendasi default teratas untuk form
                $default_pilihan = $KategoriCocok->first();

                return response()->json([
                    'id'      => $default_pilihan->id,
                    'nama'    => $default_pilihan->nama_jenis,
                    'harga'   => $default_pilihan->harga_per_kg, 
                    'akurasi' => $akurasi,
                    // Kirim semua record yang cocok ke frontend untuk diisi ke datalist/select
                    // Jadi kalau 'trash', ketiga data organik/B3 akan terkirim semua secara dinamis!
                    'opsi_pilihan' => $KategoriCocok 
                ], 200);
            }

            return response()->json(['error' => "AI mendeteksi '{$kategori_ai}', tetapi label tersebut belum didaftarkan di database master."], 404);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan sistem internal: ' . $e->getMessage()], 500);
        }
    }

    // ==========================================
    // FUNGSI 2: EKSEKUSI SIMPAN MASAL (MENJADI 1 SESI)
    // ==========================================
    public function simpanSetoran(Request $request)
    {
        set_time_limit(120);
        $items = $request->input('items');

        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'Daftar scan sampah masih kosong.'], 400);
        }

        DB::beginTransaction();
        try {
            // 1. BIKIN 1 BAGIAN INDUK
            $setoran = new \App\Models\Setoran();
            $setoran->kode_transaksi = 'TRX-' . strtoupper(Str::random(8));
            $setoran->user_id        = Auth::id(); 
            $setoran->admin_id       = 1;          
            $setoran->total_harga    = 0;          
            $setoran->total_berat    = 0;          
            $setoran->save();

            $total_harga_sesi = 0;
            $total_berat_sesi = 0;

            // 2. LOOPING UNTUK MENANGKAP BANYAK DATA & GAMBAR
            foreach ($items as $item) {
                $raw_foto   = $item['foto'];
                $foto_clean = Str::after($raw_foto, 'base64,');
                $nama_file  = 'sampah_' . uniqid() . '.jpg';
                
                Storage::disk('public')->put('foto_setoran/' . $nama_file, base64_decode($foto_clean));

                // 3. Masukkan ke Anak Tabel (detail_setorans)
                $detail = new \App\Models\DetailSetoran();
                $detail->setoran_id   = $setoran->id; 
                $detail->kategori_id  = $item['kategori_id'];
                $detail->berat        = $item['berat'];
                $detail->subtotal     = $item['berat'] * $item['harga'];
                $detail->path_foto    = 'foto_setoran/' . $nama_file; 
                $detail->save();

                $total_harga_sesi += $detail->subtotal;
                $total_berat_sesi += $detail->berat;
            }

            // 4. Update Total Harga dan Berat Keseluruhan di Tabel Induk
            $setoran->total_harga = $total_harga_sesi;
            $setoran->total_berat = $total_berat_sesi;
            $setoran->save();

            // =============================================================
            // 🔥 BARU: UPDATE SALDO TABUNGAN NASABAH SECARA OTOMATIS 🔥
            // =============================================================
            // Ambil data user/nasabah yang saat ini sedang melakukan scan (login)
            $user = Auth::user(); 
            
            // Tambahkan saldo lama dengan total harga dari sesi scan ini
            // (Catatan: Ganti 'saldo' sesuai dengan nama kolom saldo di tabel users milikmu, misal 'saldo_tabungan')
            $user->saldo = $user->saldo + $total_harga_sesi; 
            $user->save();
            // =============================================================

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()], 500);
        }
    }
}
