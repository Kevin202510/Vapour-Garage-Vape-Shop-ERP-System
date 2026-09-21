<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class DocumentationWebController extends Controller
{
    /**
     * Single-page reference covering the whole application — architecture,
     * modules, roles/permissions, branch scoping, and scheduled jobs. Static
     * content, available to any authenticated user regardless of permissions.
     */
    public function index()
    {
        return view('pages.documentation.index');
    }
}
