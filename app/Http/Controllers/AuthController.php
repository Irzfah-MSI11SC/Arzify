<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kasir;

class AuthController extends Controller
{
    // Halaman login
    public function loginPage()
    {
        return view('auth.login'); // resources/views/auth/login.blade.php
    }

    // Proses login
    public function login(Request $r)
    {
        $cred = $r->validate([
            'username' => ['required','string','max:50'],
            'password' => ['required','string','min:3'],
        ]);

        $kasir = Kasir::where('username', $cred['username'])->first();
        if (!$kasir) {
            return back()->with('error','Akun tidak ditemukan.');
        }

        $input  = (string) $cred['password'];
        $stored = (string) ($kasir->password ?? '');

        $ok = false;
        if ($stored !== '') {
            $info = password_get_info($stored); // cek hash bcrypt/argon atau bukan
            if ($info['algo'] !== 0) {
                // password di DB sudah hash
                $ok = password_verify($input, $stored);
            } else {
                // password di DB masih plain text lama
                $ok = hash_equals($stored, $input);
            }
        }

        if (!$ok) {
            return back()->with('error','Password salah.');
        }

        // simpan sesi login kasir
        $r->session()->put('kasir_id',   $kasir->idkasir ?? $kasir->id ?? null);
        $r->session()->put('kasir_nama', $kasir->nama ?? $kasir->username ?? 'Kasir');
        $r->session()->regenerate();

        return redirect()->route('dashboard')->with('success','Selamat datang, '.$kasir->nama.'!');
    }

    // Proses logout
    public function logout(Request $r)
    {
        // hapus data sesi
        $r->session()->forget(['kasir_id','kasir_nama']);
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        // kembali ke halaman login
        return redirect()->route('login')->with('success','Anda telah logout.');
    }
}
