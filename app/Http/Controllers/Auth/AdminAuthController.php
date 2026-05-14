<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BantuanGizi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function laporanPrint(Request $request): View
    {
        $query = BantuanGizi::with('lansia');

        if ($request->filled('rw')) {
            $query->whereHas('lansia', fn ($q) => $q->where('rw', $request->rw));
        }

        if ($request->filled('jenis') && $request->jenis !== 'semua') {
            $query->where('status_penerima', $request->jenis);
        }

        $data = $query->get();

        return view('livewire.admin.laporan-print', compact('data'));
    }
}
