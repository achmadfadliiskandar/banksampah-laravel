<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use App\Models\KategoriSampah;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    // 1. TAMPILKAN SEMUA DATA
    public function index()
    {
        $kategori = KategoriSampah::latest()->get();
        return view('kategori.index', compact('kategori'));
    }

    // 2. SIMPAN DATA BARU
    public function store(Request $request)
    {
        $request->validate([
            'kode_kategori' => 'required',
            'nama_jenis' => 'required|unique:kategori_sampahs',
            'tipe' => 'required',
            'harga_per_kg' => 'required|numeric',
        ]);

        KategoriSampah::create($request->all());

        return back()->with('success', 'Kategori sampah berhasil ditambahkan!');
    }

    // 3. UPDATE DATA
    public function update(Request $request, $id)
    {
        $kategori = KategoriSampah::findOrFail($id);
        
        $request->validate([
            'nama_jenis' => 'required|unique:kategori_sampahs,nama_jenis,'. $id,
            'tipe' => 'required',
            'harga_per_kg' => 'required|numeric',
        ]);

        $kategori->update($request->all());

        return back()->with('success', 'Kategori berhasil diperbarui!');
    }

    // 4. HAPUS DATA
    public function destroy($id)
    {
        try{
            $kategori = KategoriSampah::findOrFail($id);
            $kategori->delete();
            return back()->with('success', 'Kategori berhasil dihapus!');
        }catch(QueryException $e){
            // Memeriksa apakah eror disebabkan oleh kode 23000 (Hubungan Relasi/Foreign Key)
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), '1451')) {
                return redirect()->back()->with('error', 'Gagal menghapus! Kategori ini tidak bisa dihapus karena sudah memiliki riwayat transaksi setoran di database.');
            }
            // Jika ada eror database lainnya
            return redirect()->back()->with('error', 'Terjadi kesalahan pada database sistem.');
        }
    }
}