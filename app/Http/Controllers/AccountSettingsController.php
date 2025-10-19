<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccountSettingsController extends Controller
{
    /**
     * Show the account settings page for the authenticated user.
     */
    public function show(Request $request)
    {
        $user = $request->user();

        return view('sakip.settings.account', [
            'user' => $user,
        ]);
    }
}