<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordSetupController extends Controller
{
    public function show(Request $request, string $token)
    {
        $email = $request->query('email', '');

        if (! $email || ! User::where('email', $email)->exists()) {
            abort(404);
        }

        return view('auth.create-password', compact('token', 'email'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('success', 'Password created successfully! You can now sign in.');
        }

        return back()->withErrors(['password' => __($status)]);
    }
}
