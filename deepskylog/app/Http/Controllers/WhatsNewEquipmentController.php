<?php

namespace App\Http\Controllers;

use App\Models\Eyepiece;
use App\Models\Instrument;
use App\Services\EquipmentGain;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * "What's new for me" of the observer's own telescope or eyepiece
 * (/instrument/{user}/{instrument}/whats-new,
 * /eyepiece/{user}/{eyepiece}/whats-new): the page of the Livewire
 * WhatsNewEquipment. Another observer's equipment is not found.
 */
class WhatsNewEquipmentController extends Controller
{
    public function instrument(string $userSlug, string $instrumentSlug): View
    {
        return $this->show(EquipmentGain::INSTRUMENT, Instrument::class, $userSlug, $instrumentSlug);
    }

    public function eyepiece(string $userSlug, string $eyepieceSlug): View
    {
        return $this->show(EquipmentGain::EYEPIECE, Eyepiece::class, $userSlug, $eyepieceSlug);
    }

    private function show(string $kind, string $model, string $userSlug, string $slug): View
    {
        $user = Auth::user();
        abort_unless($user && $user->slug === $userSlug, 404);
        $equipment = $model::where('user_id', $user->id)->where('slug', $slug)->firstOrFail();

        return view('equipment.whats-new', ['kind' => $kind, 'equipment' => $equipment]);
    }
}
