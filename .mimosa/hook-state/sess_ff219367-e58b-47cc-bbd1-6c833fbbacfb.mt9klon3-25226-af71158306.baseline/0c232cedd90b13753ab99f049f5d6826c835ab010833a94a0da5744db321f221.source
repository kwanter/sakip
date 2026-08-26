<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class EmailVerificationController extends Controller
{
    /**
     * Display the email verification notice or redirect if already verified.
     */
    public function notice(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('sakip.dashboard');
        }
        return view('auth.verify-email');
    }

    /**
     * Handle the verification link and mark the user's email as verified.
     * This uses Laravel's built-in EmailVerificationRequest which validates the signed URL.
     */
    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('sakip.dashboard'));
        }

        if ($request->user()->markEmailAsVerified()) {
            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'email.verified',
                'details' => [
                    'email' => $request->user()->email,
                    'verified_at' => now(),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return redirect()->intended(route('sakip.dashboard'))->with('success', 'Email verified successfully!');
    }

    /**
     * Resend the verification email to the authenticated user.
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('sakip.dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}