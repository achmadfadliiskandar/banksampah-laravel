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
        $setoran = Setoran::with(['user', 'details.kategori'])->latest()->paginate(5);
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
            // User::find($request->user_id)->increment('saldo', $grandTotalHarga);

            return back()->with('success', 'Transaksi berhasil!');
        });
    }

    public function update(Request $request, $id)
    {
        // Hitung batas tanggal minimal (2 hari yang lalu jam 00:00:00) dan batas maksimal (hari ini)
        $minDate = Carbon::now()->subDays(2)->startOfDay()->toDateString();
        $maxDate = Carbon::now()->endOfDay()->toDateString();

        // 1. Validasi Input Data Array Berpasangan & Tanggal Setoran
        $request->validate([
            'tanggal_setoran' => [
                'required',
                'date',
                'after_or_equal:' . $minDate,
                'before_or_equal:' . $maxDate,
            ],
            'detail_id'     => 'required|array',
            'detail_id.*'   => 'required|exists:detail_setorans,id',
            'kategori_id'   => 'required|array',
            'kategori_id.*' => 'required|exists:kategori_sampahs,id',
            'berat'         => 'required|array',
            'berat.*'       => 'required|numeric|min:0.01',
        ], [
            'tanggal_setoran.after_or_equal'  => 'Tanggal setoran tidak boleh lebih dari 2 hari yang lalu!',
            'tanggal_setoran.before_or_equal' => 'Tanggal setoran tidak boleh memilih tanggal masa depan!',
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
                $detail->save();

                // Akumulasikan nilai baru
                $baruTotalHarga += $subtotalBaru;
                $baruTotalBerat += $beratAktual;
            }

            // 4. Update Kembali Data Akumulasi Akhir & Tanggal di Tabel Induk Transaksi
            // Menjaga jam & detik asli transaksi, hanya mengubah bagian tanggal (Y-m-d)
            $jamDetikAsli = $setoran->created_at->format('H:i:s');
            $waktuBaru    = Carbon::parse($request->tanggal_setoran . ' ' . $jamDetikAsli);

            $setoran->total_harga = $baruTotalHarga;
            $setoran->total_berat = $baruTotalBerat;
            $setoran->created_at  = $waktuBaru; // Update tanggal transaksi sesuai input admin
            $setoran->save();

            // 5. LOGIKA UTAMA: REKALKULASI SALDO REKENING NASABAH
            if ($setoran->status == 'success') {
                $nasabah->saldo = ($nasabah->saldo - $totalHargaLama) + $baruTotalHarga;
                $nasabah->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Data transaksi ' . $setoran->kode_transaksi . ' berhasil dikoreksi! Tanggal & saldo nasabah otomatis disesuaikan.');

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
            // Ubah status transaksi menjadi sesuai kiriman form (success / cancelled)
            $setoran->status = $request->status;
            $setoran->save();

            // 🔥 MODIFIKASI BARU: Jika admin memvalidasi status menjadi 'success'
            if ($request->status == 'success') {
                // Cari user/nasabah pemilik transaksi ini, lalu tambahkan saldonya
                $nasabah = User::findOrFail($setoran->user_id);
                $nasabah->increment('saldo', $setoran->total_harga);
            }

            // Note Akademis Skripsi: 
            // Saldo nasabah baru akan bertambah secara rill ketika Admin Loket 
            // menekan tombol validasi sukses setelah fisik sampah diverifikasi sesuai kriteria.
            // Ini menerapkan prinsip sinkronisasi data fisik dan data digital (Cyber-Physical System).

            DB::commit();

            // Kembalikan ke halaman sebelumnya dengan notifikasi sukses ala Bootstrap
            return redirect()->back()->with('success', 'Fisik sampah berhasil diverifikasi dan saldo nasabah telah ditambahkan! Status transaksi resmi: ' . strtoupper($request->status));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui status transaksi: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        // 1. Cari data setoran/transaksi beserta relasi user
        $setoran = \App\Models\Setoran::findOrFail($id);
        $user = $setoran->user; 

        // 2. VALIDASI AKADEMIS: Cek aturan pembatalan 2 hari (Hanya berlaku untuk yang sudah SUCCESS)
        if ($setoran->status == 'success' && \Carbon\Carbon::parse($setoran->created_at)->diffInDays(\Carbon\Carbon::now()) >= 2) {
            return redirect()->back()->with('error', 'Transaksi yang sudah sukses lebih dari 2 hari tidak bisa dibatalkan!');
        }

        // 3. VALIDASI SALDO: Hanya dicek jika statusnya sudah 'success'
        // Jika status masih pending, saldo nasabah belum bertambah, jadi tidak perlu cek kecukupan saldo.
        if ($setoran->status == 'success' && $user->saldo < $setoran->total_harga) {
            return redirect()->back()->with('error', 'Transaksi gagal dibatalkan! Saldo nasabah sudah ditarik atau tidak mencukupi untuk pemotongan kembali.');
        }

        DB::beginTransaction();
        try {
            // 🔥 POIN KUNCI: Cek status transaksi sebelum menghapus
            if ($setoran->status == 'success') {
                // Jika sudah sukses, kurangi kembali saldo nasabah karena transaksi dibatalkan
                $user->saldo = $user->saldo - $setoran->total_harga;
                $user->save();
            } 
            // Jika statusnya 'pending', blok IF di atas dilewati (saldo user tetap utuh tidak berkurang)

            // 5. Hapus detail dan data induk transaksi
            $setoran->details()->delete(); 
            $setoran->delete();

            DB::commit();

            // Berikan notifikasi dinamis sesuai kondisi status sebelumnya
            $pesanSukes = $setoran->status == 'success' 
                ? 'Transaksi sukses dibatalkan dan saldo nasabah otomatis dikurangi kembali.' 
                : 'Transaksi pending berhasil dihapus tanpa mengubah saldo nasabah.';

            return redirect()->back()->with('success', $pesanSukes);

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