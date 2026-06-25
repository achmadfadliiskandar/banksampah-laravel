<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Menampilkan halaman form login
    public function showLogin(Request $request)
    {
        // return view('auth.login');
        return view('auth.login');
    }

    private function isMobileDevice()
    {
        $userAgent = request()->header('User-Agent');
        return preg_match('/(android|iphone|ipad|mobile)/i', $userAgent);
    }

    // Memproses data dari form login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Coba login dan pastikan dia adalah ADMIN
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            if (Auth::user()->role === 'admin') {
                return redirect()->intended('/dashboard');
            }
            if (Auth::user()->role === 'nasabah') {
                return redirect()->intended('/pwa/home');
            } else {
                // Jika nasabah mencoba login ke web admin, tolak!
                Auth::logout();
                return back()->withErrors(['email' => 'Akses ditolak. Role Tidak Sesuai']);
            }
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    // Proses logout web
    public function logout(Request $request)
    {
        $role = auth()->user()->role;
    
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    
        // Jika yang logout adalah nasabah, arahkan ke login PWA
        if ($role === 'nasabah') {
            return redirect('/login')->with('success', 'Berhasil keluar dari aplikasi.');
        }
    
        return redirect('/login');
    }
}