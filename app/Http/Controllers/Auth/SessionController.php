<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class SessionController extends Controller
{
    public function create(Request $request)
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => false,
            'status' => $request->session()->get('status'),
        ]);
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        $staff = Staff::where('StaffEMAIL', $credentials['email'])->first();
        if ($staff && Hash::check($credentials['password'], $staff->StaffPASSWORD)) {
            Auth::guard('staff')->login($staff, $remember);
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        if (Auth::guard('web')->attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $remember)) {
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ]);
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        Auth::guard('staff')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
