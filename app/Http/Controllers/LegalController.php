<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LegalController extends Controller
{
    /**
     * Display the privacy policy page.
     *
     * @return \Illuminate\View\View
     */
    public function privacyPolicy()
    {
        return view('sakip.legal.privacy-policy');
    }

    /**
     * Display the terms of service page.
     *
     * @return \Illuminate\View\View
     */
    public function termsOfService()
    {
        return view('sakip.legal.terms-of-service');
    }

    /**
     * Display the disclaimer page.
     *
     * @return \Illuminate\View\View
     */
    public function disclaimer()
    {
        return view('sakip.legal.disclaimer');
    }

    /**
     * Display the accessibility page.
     *
     * @return \Illuminate\View\View
     */
    public function accessibility()
    {
        return view('sakip.legal.accessibility');
    }
}