<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        // Ambil semua user dengan role nasabah
        $nasabah = User::where('role', 'nasabah')->latest()->paginate(5);
        return view('users.index', compact('nasabah'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        try {
            // Generate Kode Nasabah Otomatis: NSB-001, NSB-002, dst.
            $count = User::where('role', 'nasabah')->count();
            $kode = 'NSB-' . date('Ymd') . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role' => 'nasabah', // Pastikan role-nya diset
                'kode_nasabah' => $kode,
                'saldo' => 0
            ]);

            return redirect()->back()->with('success', 'Nasabah baru berhasil didaftarkan dengan kode: ' . $kode);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambah nasabah: ' . $e->getMessage());
        }
    }

    public function topUpSaldo(Request $request, $id)
    {
        // Validasi input nominal minimal Rp 1.000
        $request->validate([
            'nominal' => 'required|integer|min:1000'
        ]);

        $user = User::findOrFail($id);

        DB::beginTransaction();
        try {
            // Logika matematika penambahan saldo
            $user->saldo = $user->saldo + $request->nominal;
            $user->save();

            DB::commit();
            return redirect()->back()->with('success', 'Top Up berhasil! Saldo ' . $user->name . ' telah ditambahkan sebesar Rp ' . number_format($request->nominal, 0, ',', '.'));
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal melakukan Top Up: ' . $e->getMessage());
        }
    }

    public function tarikSaldo(Request $request, $id)
    {
        $request->validate([
            'nominal' => 'required|numeric|min:1000',
        ]);

        $user = User::findOrFail($id);

        // Validasi apakah saldo cukup
        if ($user->saldo < $request->nominal) {
            return back()->with('error', 'Saldo tidak mencukupi untuk penarikan ini.');
        }

        DB::transaction(function () use ($user, $request) {
            // Kurangi saldo user
            $user->decrement('saldo', $request->nominal);

            // Opsional: Di sini kamu bisa menambahkan Log ke tabel 'transaksi_keluar' 
            // agar riwayat penarikan tercatat di database untuk laporan skripsi.
        });

        return back()->with('success', 'Penarikan saldo sebesar Rp ' . number_format($request->nominal, 0, ',', '.') . ' berhasil!');
    }
    // reset password
    public function resetPassword(Request $request, $id)
    {
        // Validasi password baru minimal 6 karakter
        $request->validate([
            'password' => 'required|string|min:8',
        ], [
            'password.min' => 'Password baru minimal harus 8 karakter.'
        ]);

        $user = User::findOrFail($id);

        // Timpa password lama dengan password baru yang di-Hash
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Password untuk nasabah ' . $user->name . ' berhasil direset!');
    }
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Opsional: Cek jika ada saldo tersisa agar tidak asal hapus
            if($user->saldo > 0) {
                return redirect()->back()->with('error', 'Nasabah masih memiliki saldo, harap kosongkan saldo sebelum menghapus.');
            }

            $user->delete();
            return redirect()->back()->with('success', 'Data nasabah berhasil dihapus permanen.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data: karena masih ada saldo/riwayat transaksi');
        }
    }
}