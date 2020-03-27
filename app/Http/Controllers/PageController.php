<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    function home()
    {
        return view('welcome');
    }

    function privacy()
    {
        return view('privacy');
    }
}
