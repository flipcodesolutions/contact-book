<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    /**
     * Show the form that asks for an email address.
     */
    public function create()
    {
        return view('auth.forgot-password');
    }

    /**
     * Email a reset link. The mailer in .env decides where that email goes.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Enter your email address.',
            'email.email' => 'Enter a valid email address.',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'If that email is on an account, a reset link has been sent.');
        }

        return back()->withErrors([
            'email' => __($status),
        ])->onlyInput('email');
    }

    /**
     * Show the form that sets a new password. The token comes from the email link.
     */
    public function edit(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /**
     * Save the new password when the token is still valid.
     */
    public function update(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'regex:/[A-Za-z]/', 'regex:/[0-9]/', 'confirmed'],
        ], [
            'email.required' => 'Enter your email address.',
            'password.required' => 'Enter a new password.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.regex' => 'The password must include letters and numbers.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, $password) {
                $user->password = $password;
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Your password has been reset. You can log in now.');
        }

        return back()->withErrors([
            'email' => __($status),
        ])->onlyInput('email');
    }
}
