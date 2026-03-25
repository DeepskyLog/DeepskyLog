<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        // The Livewire component handles querying and rendering the page.
        return view('catalogs.index');
    }
}
