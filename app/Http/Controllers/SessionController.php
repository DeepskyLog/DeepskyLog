<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SessionController extends Controller
{
    public function createSession(Request $request)
    {
        $request->session()->put('date', $request->input('date'));

        return redirect()->back();
    }
}
