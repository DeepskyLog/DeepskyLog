<?php

namespace App\Livewire\Profile;

use App\Services\DetectionThreshold;
use Livewire\Component;

/**
 * "Your own eye" on the profile page: the observer's own detection threshold
 * (DetectionThreshold), refitted when their observations changed, with the
 * choice to use it in Tonight's best, Next for you and What's new for me.
 */
class OwnEye extends Component
{
    /** The reserves the chart shows; the bins outside are left out. */
    public const CHART_RANGE = [-1.5, 2.0];

    public bool $useThreshold = false;

    public function mount(): void
    {
        $this->useThreshold = (bool) auth()->user()->use_detection_threshold;
    }

    /**
     * Refits a stale threshold after the page is shown: at once for an
     * observer without enough observations not seen (one query), else on
     * the queue, polled by the view.
     */
    public function refresh(): void
    {
        $user = auth()->user();
        if (! DetectionThreshold::stale($user)) {
            return;
        }
        if (DetectionThreshold::notSeenCount($user) < DetectionThreshold::MIN_NOT_SEEN) {
            DetectionThreshold::compute($user);
        } else {
            DetectionThreshold::queue($user);
        }
    }

    public function updatedUseThreshold(): void
    {
        auth()->user()->forceFill(['use_detection_threshold' => $this->useThreshold])->save();
    }

    /**
     * The distributions of the seen and not seen observations over the
     * contrast reserve, each as fractions of its own total, per bin.
     *
     * @return list<array{from: float, to: float, seen: float, not_seen: float}>
     */
    private function distributions(array $fit): array
    {
        $bins    = array_values(array_filter($fit['bins'] ?? [], fn (array $bin) => $bin['from'] >= self::CHART_RANGE[0] && $bin['to'] <= self::CHART_RANGE[1]));
        $seen    = max(1e-9, array_sum(array_column($bins, 'seen')));
        $notSeen = max(1e-9, array_sum(array_column($bins, 'not_seen')));

        return array_map(fn (array $bin) => [
            'from'     => (float) $bin['from'],
            'to'       => (float) $bin['to'],
            'seen'     => $bin['seen']     / $seen,
            'not_seen' => $bin['not_seen'] / $notSeen,
        ], $bins);
    }

    public function render()
    {
        $user = auth()->user()->fresh();
        $fit  = DetectionThreshold::stored($user);

        return view('livewire.profile.own-eye', [
            'fit'           => $fit,
            'pending'       => DetectionThreshold::pending($user),
            'distributions' => $fit && $fit['threshold'] !== null ? $this->distributions($fit) : [],
            'range'         => self::CHART_RANGE,
            'offset'        => DetectionThreshold::offsetFor($user),
        ]);
    }
}
