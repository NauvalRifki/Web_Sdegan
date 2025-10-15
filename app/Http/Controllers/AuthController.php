<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm(){
        return view('/login');
    }

    public function login(Request $request){
        $request->validate([
            'id' => 'required|integer',
            'password' => 'required|min:8',
        ], [
            'id.required' => 'ID wajib diisi',
            'id.integer' => 'ID harus berupa angka',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password harus minimal 8 karakter',
        ]);

        $credentials = $request->only('id', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->role == 'admin') {
                return redirect()->route('dashboard_admin.index');
            } elseif ($user->role == 'operator') {
                return redirect()->route('dashboard_operator.index');
            } elseif ($user->role == 'verifikator') {
                return redirect()->route('dashboard_verifikator.index');
            } else {
                Auth::logout();
                return redirect('/')->withErrors('Role tidak dikenali!');
            }
        } else {
            return back()->withErrors('ID atau password yang Anda masukkan tidak sesuai')->withInput();
        }
    }

    public function logout(Request $request){
        Auth::logout(); // Logout pengguna
    
        $request->session()->invalidate(); // Hapus sesi
    
        $request->session()->regenerateToken(); // Regenerasi token CSRF
    
        return redirect('/'); // Redirect ke halaman utama
    }
}