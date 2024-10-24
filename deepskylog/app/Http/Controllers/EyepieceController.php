<?php

namespace App\Http\Controllers;

use App\Models\EyepiecesOld;

class EyepieceController extends Controller
{
    public function show_from_user(string $user_id)
    {
        return EyepiecesOld::where('observer', $user_id)->get();
    }
}
