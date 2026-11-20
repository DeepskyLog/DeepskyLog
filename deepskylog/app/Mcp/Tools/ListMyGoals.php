<?php

namespace App\Mcp\Tools;

use App\Models\ObservingGoal;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('The observer\'s observing goals (their own, the challenges they joined and the beginner path): what to see or draw by when, how many objects they have so far, and the status (active, upcoming, reached, ended).')]
#[IsReadOnly]
class ListMyGoals extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $user = $request->user();

        $goals = ObservingGoal::shownFor($user, $user)->map(function (ObservingGoal $goal) {
            $progress = $goal->progress();

            return array_filter([
                'title'             => $goal->title(),
                'mode'              => $goal->drawings() ? 'draw' : 'see',
                'source'            => html_entity_decode((string) $goal->source_label, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'from'              => $goal->starts_on->toDateString(),
                'until'             => $goal->ends_on->toDateString(),
                'new_objects_only'  => $goal->new_only ?: null,
                'challenge'         => $goal->challenge_id ? true : null,
                'beginner_path'     => $goal->beginner_path ?: null,
                'public'            => (bool) $goal->public,
                'count'             => $progress['count'],
                'target'            => $progress['target'],
                'objects_in_source' => $progress['total'],
                'status'            => $progress['status'],
                'reached_on'        => $progress['reached_on']?->toDateString(),
                'days_left'         => $progress['status'] === 'active' ? $progress['days_left'] : null,
            ], fn ($value) => $value !== null);
        })->values();

        return Response::structured([
            'count' => $goals->count(),
            'url'   => route('observer.show', $user->slug) . '#observing-goals',
            'goals' => $goals->all(),
        ]);
    }
}
