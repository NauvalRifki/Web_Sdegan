<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm(){
        return view('login');
    }

    public function login(Request $request){
        $key = Str::lower($request->input('id')).'|'.$request->ip();

        // Batasi maksimal 3 percobaan login gagal
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors(['error' => "Terlalu banyak percobaan login. Coba lagi dalam $seconds detik."]);
        }

        $request->validate([
            'id' => 'required|integer',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/', 
            ],
        ], [
            'id.required' => 'ID wajib diisi',
            'id.integer' => 'ID harus berupa angka',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, dan angka',
        ]);

        $credentials = $request->only('id', 'password');

        if (Auth::attempt($credentials)) {
            RateLimiter::clear($key);
            $request->session()->regenerate();

            $user = Auth::user();

            // Cek masa berlaku password
            if ($user->password_expires_at && now()->greaterThan($user->password_expires_at)) {
                Auth::logout();
                return redirect('/login')->withErrors('Password Anda telah kedaluwarsa. Silakan ubah password.');
            }

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
            RateLimiter::hit($key);
            return back()->withErrors('ID atau password yang Anda masukkan tidak sesuai')->withInput();
        }
    }

    public function logout(Request $request){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function showForgotForm()
    {
        return view('forgot_password');
    }

public function processForgotPassword(Request $request) {
    $request->validate([
        'id' => 'required|integer|exists:pengguna,id',
        'new_password' => [
            'required',
            'string',
            'min:8',
            'regex:/[A-Z]/',
            'regex:/[a-z]/',
            'regex:/[0-9]/',
        ],
    ], [
        'id.required' => 'ID wajib diisi',
        'id.exists' => 'ID tidak ditemukan',
        'new_password.required' => 'Password baru wajib diisi',
        'new_password.min' => 'Password minimal 8 karakter',
        'new_password.regex' => 'Password harus mengandung huruf besar, huruf kecil, dan angka',
    ]);

    $user = \App\Models\Pengguna::find($request->id);
    $user->password = bcrypt($request->new_password);
    $user->password_expires_at = now()->addMonths(3);
    $user->save();

    return redirect('/')->with('success', 'Password berhasil diubah. Silakan login kembali.');
}

}
