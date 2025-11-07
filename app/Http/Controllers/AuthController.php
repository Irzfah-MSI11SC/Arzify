<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Kasir;

class AuthController extends Controller
{
    public function loginPage()
    {
        return view('auth.login');
    }

    public function login(Request $r)
    {
        $cred = $r->validate([
            'username' => ['required','string','max:50'],
            'password' => ['required','string','min:1'],
        ]);

        // normalisasi input
        $username  = trim($cred['username']);
        $input     = (string) $cred['password'];
        $inputTrim = rtrim($input); // buang spasi kanan (kasus umum dari phpMyAdmin)

        $kasir = Kasir::where('username', $username)->first();
        if (!$kasir) {
            return back()->with('error','Akun tidak ditemukan.');
        }

        $stored = (string) ($kasir->password ?? '');
        $ok = false;

        if ($stored !== '') {
            $info = password_get_info($stored);

            if ($info['algo'] !== 0) {
                // SUDAH HASH (bcrypt/argon)
                $ok = password_verify($input, $stored) || password_verify($inputTrim, $stored);

                if ($ok && Hash::needsRehash($stored)) {
                    $kasir->password = Hash::make($inputTrim);
                    $kasir->save();
                }
            } else {
                // MASIH PLAIN TEXT (mis. diisi manual di phpMyAdmin)
                $storedTrim = rtrim($stored);

                if (hash_equals($stored, $input) || hash_equals($storedTrim, $inputTrim)) {
                    $ok = true;
                    // upgrade ke bcrypt
                    $kasir->password = Hash::make($inputTrim);
                    $kasir->save();
                }
            }
        }

        if (!$ok) {
            return back()->with('error','Password salah.');
        }

        // simpan sesi
        $r->session()->put('kasir_id',       $kasir->idkasir ?? $kasir->id ?? null);
        $r->session()->put('kasir_username', $kasir->username);
        $r->session()->put('kasir_nama',     $kasir->nama ?? $kasir->username ?? 'Kasir');
        $r->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Selamat datang, '.($kasir->nama ?? $kasir->username).'!');
    }

    public function loginPageLogout(Request $r) { return $this->logout($r); }

    public function logout(Request $r)
    {
        $r->session()->forget(['kasir_id','kasir_username','kasir_nama']);
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('login')->with('success','Anda telah logout.');
    }
}
