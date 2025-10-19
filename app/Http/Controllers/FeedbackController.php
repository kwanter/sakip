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
        return view('sakip.feedback.index');
    }

    /**
     * Store a new feedback submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'category' => 'required|string|max:100',
        ]);

        // Here you would typically store the feedback in the database
        // For now, we'll just flash a success message

        return redirect()->route('feedback')->with('success', 'Terima kasih! Masukan Anda telah berhasil dikirim.');
    }
}