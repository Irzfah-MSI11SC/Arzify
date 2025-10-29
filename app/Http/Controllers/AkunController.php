<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kasir;

class AkunController extends Controller
{
    public function passwordPage()
    {
        return view('akun.password');
    }

    public function updatePassword(Request $r)
    {
        $data = $r->validate([
            'password' => 'required|string|min:3|confirmed',
        ]);

        $kasir = Kasir::findOrFail((int)$r->session()->get('kasir_id'));
        $kasir->password = password_hash($data['password'], PASSWORD_BCRYPT);
        $kasir->save();

        return back()->with('success','Password diperbarui.');
    }
}
