<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Display the authenticated user's profile page.
     */
    public function show(Request $request)
    {
        $user = $request->user();

        return view('sakip.profile.show', [
            'user' => $user,
        ]);
    }
}