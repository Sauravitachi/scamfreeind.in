<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

class AccountSettingsController extends \App\Foundation\Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->refresh();

        return view('admin.account-settings.index', compact('user'));
    }
}
