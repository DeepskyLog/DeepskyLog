<?php

namespace App\Http\Controllers;

use App\Services\PopularObjectsRanking;
use Illuminate\Http\Request;

class PopularObjectController extends Controller
{
    public function index(Request $request)
    {
        $mode = $request->query('mode', 'observations');
        if (!in_array($mode, PopularObjectsRanking::MODES, true)) {
            $mode = 'observations';
        }

        return view('observations.popular-objects', ['mode' => $mode]);
    }
}
