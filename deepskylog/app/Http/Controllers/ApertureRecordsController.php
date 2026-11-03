<?php

namespace App\Http\Controllers;

use App\Services\Observations\ApertureRecords;
use Illuminate\Http\Request;

/**
 * The faintest deep-sky objects seen with at most an aperture, and the
 * observers who saw the faintest (App\Services\Observations\ApertureRecords).
 */
class ApertureRecordsController extends Controller
{
    public function index(Request $request, ApertureRecords $records)
    {
        $limit = $request->query('aperture');
        $limit = ApertureRecords::isLimit($limit) ? (int) $limit : ApertureRecords::DEFAULT_LIMIT;

        $objects = $records->ranking($limit);
        $observers = $records->observers($limit);

        return view('records.aperture', [
            'limit' => $limit,
            'objects' => $objects,
            'observers' => $observers,
            'observations' => ApertureRecords::observationsOf($objects, $observers),
            'inches' => (bool) ($request->user()?->showInches ?? false),
        ]);
    }
}
