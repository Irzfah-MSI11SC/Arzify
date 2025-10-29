<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kasir;

class AuthController extends Controller
{
    public function loginPage() {
    // cari: resources/views/auth/login.blade.php
    return view('auth.login');
}

    public function login(Request $r) {
        $cred = $r->validate([
            'username' => ['required','string','max:50'],
            'password' => ['required','string','min:3'],
        ]);

        $kasir = Kasir::where('username', $cred['username'])->first();
        if (!$kasir) return back()->with('error','Akun tidak ditemukan.');

        $input  = (string) $cred['password'];
        $stored = (string) ($kasir->password ?? '');

        $ok = false;
        if ($stored !== '') {
            $info = password_get_info($stored);
            if (($info['algo'] ?? 0) !== 0) {
                $ok = password_verify($input, $stored);
            } else {
                $ok = hash_equals($stored, $input);
                if ($ok) {
                    $kasir->password = password_hash($input, PASSWORD_BCRYPT);
                    $kasir->save();
                }
            }
        }

        if (!$ok) return back()->with('error','Username atau password salah.');

        $r->session()->put('kasir_id', $kasir->idkasir);
        $r->session()->put('kasir_username', $kasir->username);
        $r->session()->put('kasir_nama', $kasir->nama);

        return redirect()->route('dashboard');
    }

    public function logout(Request $r) {
        $r->session()->flush();
        return redirect()->route('login')->with('success','Anda telah logout.');
    }
}
