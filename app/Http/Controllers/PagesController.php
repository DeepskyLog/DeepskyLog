<?php

namespace App\Http\Controllers;

class PagesController extends Controller
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
