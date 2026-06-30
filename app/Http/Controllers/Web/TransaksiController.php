<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Setoran;
use App\Models\User;
use App\Models\KategoriSampah;
use App\Models\DetailSetoran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TransaksiController extends Controller
{
    public function index()
    {
        // Eager load relasi berjenjang: Setoran -> Details -> Kategori
        $setoran = Setoran::with(['user', 'details.kategori'])->latest()->get();
        $nasabah = User::where('role', 'nasabah')->get();
        $kategori = KategoriSampah::all();

        return view('transaksi.index', compact('setoran', 'nasabah', 'kategori'));
    }

    public function create()
    {
        return view('transaksi.create');
    }

    public function store(Request $request)
    {
        // Validasi array
        $request->validate([
            'user_id' => 'required',
            'kategori_id' => 'required|array',
            'berat' => 'required|array',
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Buat Header Dulu
            $setoran = Setoran::create([
                'kode_transaksi' => 'TRX-' . time(),
                'user_id' => $request->user_id,
                'admin_id' => auth()->id(),
                'total_berat' => 0, // Akan diupdate di bawah
                'total_harga' => 0,
            ]);

            $grandTotalHarga = 0;
            $grandTotalBerat = 0;

            // 2. Loop Array Detail
            foreach ($request->kategori_id as $key => $kat_id) {
                $kategori = KategoriSampah::findOrFail($kat_id);
                $beratItem = $request->berat[$key];
                $subtotal = $kategori->harga_per_kg * $beratItem;

                $setoran->details()->create([
                    'kategori_id' => $kat_id,
                    'berat' => $beratItem,
                    'subtotal' => $subtotal,
                ]);

                $grandTotalHarga += $subtotal;
                $grandTotalBerat += $beratItem;
            }

            // 3. Update Header dengan Total Akhir
            $setoran->update([
                'total_berat' => $grandTotalBerat,
                'total_harga' => $grandTotalHarga,
            ]);

            // 4. Update Saldo Nasabah
            User::find($request->user_id)->increment('saldo', $grandTotalHarga);

            return back()->with('success', 'Transaksi berhasil!');
        });
    }

    public function update(Request $request, $id)
    {
        // 1. Validasi Input Data Array Berpasangan
        $request->validate([
            'detail_id'     => 'required|array',
            'detail_id.*'   => 'required|exists:detail_setorans,id', // Ganti nama tabel anak jika berbeda
            'kategori_id'   => 'required|array',
            'kategori_id.*' => 'required|exists:kategori_sampahs,id',
            'berat'         => 'required|array',
            'berat.*'       => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();

        try {
            // 2. Cari Data Induk Transaksi
            $setoran = Setoran::findOrFail($id);
            $nasabah = User::findOrFail($setoran->user_id);

            // Simpan data total harga lama sebelum dikoreksi untuk menghitung selisih saldo nasabah
            $totalHargaLama = $setoran->total_harga;

            $baruTotalHarga = 0;
            $baruTotalBerat = 0;

            // 3. Looping Array untuk Mengupdate Setiap Baris Item Sampah
            foreach ($request->detail_id as $index => $detailId) {
                $katId        = $request->kategori_id[$index];
                $beratAktual  = $request->berat[$index];

                // Ambil harga asli terupdate dari tabel master kategori
                $masterKategori = KategoriSampah::findOrFail($katId);
                
                // Rumus Hitung Subtotal Desimal (decimal 12,2)
                $subtotalBaru = $beratAktual * $masterKategori->harga_per_kg;

                // Cari baris anak detail dan update nilainya
                $detail = DetailSetoran::findOrFail($detailId);
                $detail->kategori_id = $katId;
                $detail->berat       = $beratAktual;    // Menyimpan format decimal(8,2)
                $detail->subtotal    = $subtotalBaru;   // Menyimpan format decimal(12,2)
                // Catatan: path_foto tidak diubah/dihapus karena fungsi ini hanya melakukan koreksi timbangan fisik lokasi
                $detail->save();

                // Akumulasikan nilai baru
                $baruTotalHarga += $subtotalBaru;
                $baruTotalBerat += $beratAktual;
            }

            // 4. Update Kembali Data Akumulasi Akhir di Tabel Induk Transaksi
            $setoran->total_harga = $baruTotalHarga;
            $setoran->total_berat = $baruTotalBerat;
            $setoran->save();

            // 5. 🔥 LOGIKA UTAMA: REKALKULASI SALDO REKENING NASABAH
            // Rumus: Saldo Sekarang dikurangi Total Lama (Reset ke awal), lalu ditambah Total Baru yang Valid
            if ($setoran->status == 'success') {
                $nasabah->saldo = ($nasabah->saldo - $totalHargaLama) + $baruTotalHarga;
                $nasabah->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Data transaksi ' . $setoran->kode_transaksi . ' berhasil dikoreksi! Saldo nasabah otomatis disesuaikan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal melakukan koreksi data: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, $id)
    {
        // 1. Validasi input request untuk memastikan status yang dikirim benar
        $request->validate([
            'status' => 'required|in:success,cancelled'
        ]);

        // 2. Cari data transaksi/setoran berdasarkan ID
        $setoran = Setoran::findOrFail($id);

        // 3. Amankan kondisi jika status di database ternyata sudah bernilai 'success'
        if ($setoran->status == 'success') {
            return redirect()->back()->with('warning', 'Transaksi ini sudah berstatus SUCCESS sebelumnya.');
        }

        // 4. Mulai transaksi database agar proses penguncian aman
        DB::beginTransaction();
        try {
            // Ubah status transaksi menjadi sesuai kiriman form (success)
            $setoran->status = $request->status;
            $setoran->save();

            // Note Akademis Skripsi: 
            // Karena saldo nasabah sudah ditambahkan secara otomatis pada saat scan di PWA (simpanSetoran),
            // maka di fungsi ini admin murni bertugas mengunci status menjadi 'success'.
            // Perubahan status menjadi 'success' ini yang akan mengaktifkan aturan perlindungan saldo 2 hari.

            DB::commit();

            // Kembalikan ke halaman sebelumnya dengan notifikasi sukses ala Bootstrap
            return redirect()->back()->with('success', 'Fisik sampah berhasil diverifikasi! Status transaksi resmi: ' . strtoupper($request->status));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui status transaksi: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        // 1. Cari data setoran/transaksi
        $setoran = \App\Models\Setoran::findOrFail($id);
        $user = $setoran->user; // Relasi ke model User
    
        // 2. VALIDASI 1: Cek apakah sudah lewat 2 hari (48 jam)
        // // Carbon::parse akan membandingkan waktu created_at dengan waktu sekarang
        // if (Carbon::parse($setoran->created_at)->diffInDays(Carbon::now()) >= 2) {
        //     return redirect()->back()->with('error', 'Transaksi gagal dibatalkan! Batas waktu pembatalan maksimal 2 hari.');
        // }

        // Jika statusnya sudah 'success' DAN sudah lewat 2 hari, BARULAH dilarang hapus
        if ($setoran->status == 'success' && Carbon::parse($setoran->created_at)->diffInDays(Carbon::now()) >= 2) {
            return redirect()->back()->with('error', 'Transaksi yang sudah sukses lebih dari 2 hari tidak bisa dibatalkan!');
        }
    
        // 3. VALIDASI 2: Cek apakah uangnya sudah diambil / saldo tidak mencukupi
        // Jika saldo user saat ini lebih kecil dari nominal yang mau dibatalkan, 
        // artinya duit di tabungannya sudah ditarik (diambil).
        if ($user->saldo < $setoran->total_harga) {
            return redirect()->back()->with('error', 'Transaksi gagal dibatalkan! Saldo nasabah sudah ditarik atau tidak mencukupi untuk pemotongan.');
        }
    
        DB::beginTransaction();
        try {
            // 4. Proses Kurangi Saldo Nasabah Kembali karena transaksi batal
            $user->saldo = $user->saldo - $setoran->total_harga;
            $user->save();
    
            // 5. Hapus detail dan data induk transaksi
            // Jika di database kamu pasang onDelete('cascade') di relasi migrasi, cukup hapus $setoran saja
            $setoran->details()->delete(); // Hapus anak tabel jika diperlukan manual
            $setoran->delete();
    
            DB::commit();
            return redirect()->back()->with('success', 'Transaksi berhasil dibatalkan dan saldo nasabah otomatis dikurangi.');
    
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    // fungsi baru scan sampah dengan ai
    public function scanAI(Request $request)
    {
        // 1. Validasi File
        $request->validate([
            'foto_sampah' => 'required|image|max:5120',
        ], [
            'foto_sampah.required' => 'Silakan pilih foto terlebih dahulu.',
            'foto_sampah.image'    => 'File harus berupa gambar.',
            'foto_sampah.max'      => 'Ukuran foto maksimal 5MB.'
        ]);

        $foto = $request->file('foto_sampah');

        try {
            // 2. Kirim Foto ke API Flask
            $response = Http::timeout(60)->attach(
                'image', 
                fopen($foto->getRealPath(), 'r'), 
                $foto->getClientOriginalName()
            )->post('http://127.0.0.1:5000/predict');

            if (!$response->successful()) {
                return back()->with('error', 'Gagal terhubung ke Server AI. Pastikan Flask di port 5000 sudah dinyalakan.');
            }

            $hasil_ai = $response->json();

            // 3. Pastikan format dari Flask sesuai (Wajib ada 'label' atau 'prediction')
            // Kita buat toleran: memeriksa 'label' atau 'prediction' jika Flask menggunakan penamaan berbeda
            $label_raw = $hasil_ai['label'] ?? $hasil_ai['prediction'] ?? null;
            
            if (!$label_raw) {
                return back()->with('error', 'Format respons dari Flask tidak sesuai standar. Data "label" tidak ditemukan.');
            }

            // Ekstrak data dari Flask
            $label_inggris = trim(strtolower($label_raw));
            $akurasi_raw = $hasil_ai['confidence'] ?? $hasil_ai['accuracy'] ?? '0';
            
            // Membersihkan karakter string agar aman dikonversi ke float
            // Mengantisipasi format "98.50%", "98.5", atau "0.985"
            $akurasi_clean = str_replace('%', '', $akurasi_raw);
            $akurasi = (float) $akurasi_clean;

            // Jika Flask mengembalikan format desimal 0-1 (misal 0.95), kalikan 100 agar menjadi persen (95)
            if ($akurasi <= 1.0 && $akurasi > 0) {
                $akurasi = $akurasi * 100;
            }

            // Simpan format string rapi untuk tampilan di view
            $akurasi_str = number_format($akurasi, 2) . '%';

            // 4. Logika Jika AI Ragu (Akurasi di bawah 60%)
            if ($akurasi < 60) {
                return back()->with('warning', "AI kurang yakin dengan objek tersebut (Tingkat keyakinan: {$akurasi_str}). Silakan foto ulang lebih dekat.");
            }
            
            // 5. 🔥 KAMUS MAPPING LENGKAP & TOLERAN (Menghindari Typo / Variasi Label Flask)
            $kamus = [
                'paper'     => 'Kertas Bekas',
                'plastic'   => 'Botol Plastik PET',
                'cardboard' => 'Kardus Bekas / Box',
                'glass'     => 'Botol Kaca Bening',
                'metal'     => 'Besi Tua / Kaleng',
                'trash'     => 'Lainya',
                
                // Antisipasi jika model MobileNetV2 milikmu mengeluarkan label variasi lain
                'kertas'    => 'Kertas Bekas',
                'plastik'   => 'Botol Plastik PET',
                'kardus'    => 'Kardus Bekas / Box',
                'kaca'      => 'Botol Kaca Bening',
                'besi'      => 'Besi Tua / Kaleng'
            ];

            // Cari nama Indonesianya dari kamus
            $nama_target = $kamus[$label_inggris] ?? null;

            // JIKA TIDAK KETEMU DI KAMUS: Berikan fallback pencarian parsial (mencari substring kata kunci)
            if (!$nama_target) {
                foreach ($kamus as $key => $value) {
                    if (str_contains($label_inggris, $key)) {
                        $nama_target = $value;
                        break;
                    }
                }
            }

            if ($nama_target) {
                // Cari data lengkapnya di database berdasarkan nama_jenis
                $kategori_db = \App\Models\KategoriSampah::where('nama_jenis', $nama_target)->first();

                if ($kategori_db) {
                    $pesan = "🤖 AI Mendeteksi: " . strtoupper($kategori_db->nama_jenis) . " (" . $akurasi_str . ")";
                    
                    return back()
                        ->with('success_ai', $pesan)
                        ->with('auto_select_id', $kategori_db->id)
                        ->with('akurasi_score', $akurasi_str);
                }
            }

            // 6. Jika kategori lolos prediksi tapi tidak terdaftar di sistem pemetaan
            return back()->with('warning', "AI mendeteksi label '{$label_raw}' ({$akurasi_str}), tetapi nama tersebut belum dipetakan ke dalam database.");

        } catch (\Exception $e) {
            // Menangkap error jika server Flask mati/RTO
            return back()->with('error', 'Terjadi kesalahan sistem: Koneksi ke server Python Flask gagal/gagal terhubung. Detail: ' . $e->getMessage());
        }
    }
}