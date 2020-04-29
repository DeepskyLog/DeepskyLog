<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function home()
    {
        return view('welcome');
    }

    public function privacy()
    {
        return view('privacy');
    }
}
