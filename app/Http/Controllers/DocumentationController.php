<?php

namespace App\Http\Controllers;

class DocumentationController extends Controller
{
    /**
     * Display the documentation page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('sakip.documentation.index');
    }
}
