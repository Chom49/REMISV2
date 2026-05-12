<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)
                         ->withInput($request->only('email'))
                         ->with('open_modal', 'login');
        }

        if (Auth::attempt($request->only('email', 'password'), true)) {
            $request->session()->regenerate();
            return $this->redirectByRole();
        }

        return back()->withErrors(['email' => 'These credentials do not match our records.'])
                     ->withInput($request->only('email'))
                     ->with('open_modal', 'login');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'role'     => ['required', 'in:landlord,tenant'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)
                         ->withInput($request->except(['password', 'password_confirmation']))
                         ->with('open_modal', 'signup');
        }

        $validated = $validator->validated();

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'role'     => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user, true);
        $request->session()->regenerate();

        return $this->redirectByRole();
    }

    public function showLock()
    {
        if (!Auth::check()) {
            return redirect()->route('home');
        }

        if (!session('auth.locked') && !Auth::viaRemember()) {
            return $this->redirectByRole();
        }

        session(['auth.locked' => true]);
        return view('auth.lock');
    }

    public function unlock(Request $request)
    {
        $request->validate(['password' => 'required']);

        $throttleKey = 'session-unlock:' . Auth::id();
        $maxAttempts = 5;

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'password' => "Too many failed attempts. Try again in {$seconds} seconds.",
            ])->with('throttled', true);
        }

        if (!Hash::check($request->password, Auth::user()->password)) {
            RateLimiter::hit($throttleKey, 60);
            $attempts  = RateLimiter::attempts($throttleKey);
            $remaining = $maxAttempts - $attempts;
            return back()->withErrors([
                'password' => $remaining > 0
                    ? "Incorrect password. {$remaining} " . ($remaining === 1 ? 'attempt' : 'attempts') . " remaining."
                    : 'Incorrect password. Please try again.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $intended = session()->pull('auth.lock.intended');
        session()->forget('auth.locked');
        $request->session()->regenerate();

        return $intended ? redirect($intended) : $this->redirectByRole();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function redirectByRole()
    {
        return Auth::user()->isLandlord()
            ? redirect()->route('landlord.dashboard')
            : redirect()->route('tenant.dashboard');
    }
}
