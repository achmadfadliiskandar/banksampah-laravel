<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\KategoriSampah;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // 1. BUAT AKUN PENGGUNA (ADMIN & NASABAH)
        // ==========================================
        
        // Akun Admin Bank Sampah
        User::create([
            'name' => 'Admin Pusat',
            'email' => 'admin@banksampah.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // Akun Nasabah (Untuk percobaan Login di Android nanti)
        User::create([
            'name' => 'Achmad Fadli Iskandar',
            'email' => 'fadli@nasabah.com',
            'password' => Hash::make('password123'),
            'role' => 'nasabah',
            'saldo' => 0,
            'kode_nasabah' => 'NSB-' . date('Ymd') . '-0001',
        ]);

        // ==========================================
        // 2. BUAT KATALOG JENIS SAMPAH (SINKRON DENGAN AI)
        // ==========================================
        $kategori = [
            // --- KELOMPOK ANORGANIK (BERNILAI UANG) ---
            [
                'kode_kategori' => 'plastic', // 🔥 Sesuai dataset
                'nama_jenis' => 'Botol Plastik PET',
                'tipe' => 'anorganik',
                'harga_per_kg' => 3000,
                'deskripsi' => 'Botol air mineral bening, botol kecap, botol sirup plastik.'
            ],
            [
                'kode_kategori' => 'cardboard', // 🔥 Sesuai dataset
                'nama_jenis' => 'Kardus Bekas / Box',
                'tipe' => 'anorganik',
                'harga_per_kg' => 1500,
                'deskripsi' => 'Kardus cokelat kering, kotak sepatu, karton packing.'
            ],
            [
                'kode_kategori' => 'glass', // 🔥 Sesuai dataset
                'nama_jenis' => 'Botol Kaca Bening',
                'tipe' => 'anorganik',
                'harga_per_kg' => 500,
                'deskripsi' => 'Botol sirup kaca, gelas kaca utuh, botol kecap kaca.'
            ],
            [
                'kode_kategori' => 'metal', // 🔥 Sesuai dataset
                'nama_jenis' => 'Besi Tua / Kaleng',
                'tipe' => 'anorganik',
                'harga_per_kg' => 4000,
                'deskripsi' => 'Potongan besi, paku, kaleng soda aluminium, kaleng susu.'
            ],
            [
                'kode_kategori' => 'paper', // 🔥 Sesuai dataset
                'nama_jenis' => 'Kertas Bekas',
                'tipe' => 'anorganik',
                'harga_per_kg' => 2000,
                'deskripsi' => 'Kertas HVS, koran, majalah, atau buku bekas.'
            ],
        
            // --- KELOMPOK ORGANIK & B3 (DITITIPKAN KE KELAS 'TRASH') ---
            [
                'kode_kategori' => 'trash', // 🔥 DIUBAH JADI trash AGAR MATCH JIKA DI-SCAN
                'nama_jenis' => 'Sisa Makanan Dapur',
                'tipe' => 'organik',
                'harga_per_kg' => 0, 
                'deskripsi' => 'Nasi basi, tulang ayam/ikan, sisa sayuran dapur.'
            ],
            [
                'kode_kategori' => 'trash', // 🔥 DIUBAH JADI trash AGAR MATCH JIKA DI-SCAN
                'nama_jenis' => 'Sampah Kebun / Ranting',
                'tipe' => 'organik',
                'harga_per_kg' => 0,
                'deskripsi' => 'Daun kering gugur, rumput liar, ranting pohon kecil.'
            ],
            [
                'kode_kategori' => 'trash', // 🔥 DIUBAH JADI trash AGAR MATCH JIKA DI-SCAN
                'nama_jenis' => 'Baterai Bekas / Aki',
                'tipe' => 'b3',
                'harga_per_kg' => 0,
                'deskripsi' => 'Baterai remote AA/AAA, baterai HP kembung, aki motor bekas.'
            ],
        ];

        foreach ($kategori as $item) {
            KategoriSampah::create($item);
        }
    }
}