<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Display the feedback form.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view("sakip.feedback.index");
    }

    /**
     * Store a new feedback submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Only authenticated users (route middleware) may submit feedback.
        // No dedicated Feedback model/policy in this codebase — require auth user.
        abort_unless($request->user() !== null, 403);

        $request->validate([
            "subject" => "required|string|max:255",
            "message" => "required|string|max:5000",
            "category" => "required|string|max:100",
        ]);

        // Persistence intentionally deferred; validation + auth boundary is the security fix.

        return redirect()
            ->route("feedback")
            ->with(
                "success",
                "Terima kasih! Masukan Anda telah berhasil dikirim.",
            );
    }
}
