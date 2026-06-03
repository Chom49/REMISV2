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
            'name'     => ['required', 'string', 'max:100', 'regex:/^[\pL\s\-\']+$/u'],
            'email'    => ['required', 'email:rfc', 'max:150', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'regex:/^\+?[0-9\s\-]{7,20}$/', 'unique:users,phone'],
            'role'     => ['required', 'in:landlord,tenant'],
            'password' => [
                'required', 'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
        ], [
            'name.required'          => 'Full name is required.',
            'name.regex'             => 'Full name may only contain letters, spaces, hyphens, or apostrophes.',
            'name.max'               => 'Full name must not exceed 100 characters.',
            'email.required'         => 'Email address is required.',
            'email.email'            => 'Please enter a valid email address.',
            'email.unique'           => 'This email address is already registered. Please sign in instead.',
            'phone.regex'            => 'Phone number must contain only digits, spaces, or hyphens (7–20 characters).',
            'phone.unique'           => 'This phone number is already associated with an existing account.',
            'password.required'      => 'Password is required.',
            'password.confirmed'     => 'Passwords do not match.',
            'password.min'           => 'Password must be at least 8 characters.',
            'password.mixed_case'    => 'Password must contain at least one uppercase and one lowercase letter.',
            'password.numbers'       => 'Password must contain at least one number.',
            'password.symbols'       => 'Password must contain at least one special character (e.g. @, #, !, $).',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)
                         ->withInput($request->except(['password', 'password_confirmation']))
                         ->with('open_modal', 'signup');
        }

        $validated = $validator->validated();

        $user = User::create([
            'name'     => trim($validated['name']),
            'email'    => strtolower(trim($validated['email'])),
            'phone'    => $validated['phone'] ?? null,
            'role'     => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user, true);
        $request->session()->regenerate();

        $redirect = $this->redirectByRole();
        return $redirect->with('success', 'Welcome to REMIS! Your account has been created successfully.');
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
        $user = Auth::user();
        if ($user->isAdmin())    return redirect()->route('admin.dashboard');
        if ($user->isLandlord()) return redirect()->route('landlord.dashboard');
        return redirect()->route('tenant.dashboard');
    }
}
