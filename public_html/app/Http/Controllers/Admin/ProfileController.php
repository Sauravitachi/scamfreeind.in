<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

class ProfileController extends \App\Foundation\Controller
{
    public function index(Request $request)
    {
        return view('admin.profile.index');
    }
}
