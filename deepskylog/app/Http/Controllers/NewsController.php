<?php

namespace App\Http\Controllers;

use App\Jobs\TranslateNewsItem;
use App\Models\Location;
use App\Models\NewsItem;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The news (#960): /news and /news/{item} for everybody, and
 * /admin/news for the administrators and database experts, who write items
 * and hide the automatic ones (news:astronomical-events) they do not want.
 */
class NewsController extends Controller
{
    public const PER_PAGE = 10;

    public function index(): View
    {
        $items = NewsItem::published()->with('author:id,name')->orderByDesc('published_at')->orderByDesc('id')->paginate(self::PER_PAGE);

        return view('news.index', ['items' => $items, 'location' => Location::forEphemerides(Auth::user())]);
    }

    public function show(NewsItem $newsItem): View
    {
        $visible = ! $newsItem->hidden && $newsItem->published_at <= now();
        abort_unless($visible || Gate::allows('manage_news', User::class), 404);

        $newsItem->loadMissing('author:id,name');

        return view('news.show', ['item' => $newsItem, 'location' => Location::forEphemerides(Auth::user())]);
    }

    public function admin(): View
    {
        $items = NewsItem::with('author:id,name')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.news.index', ['items' => $items]);
    }

    public function create(): View
    {
        return view('admin.news.form', ['item' => new NewsItem([
            'published_at' => now(),
            'language'     => Auth::user()->language ?: config('app.locale'),
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $item = new NewsItem(['kind' => NewsItem::MANUAL, 'user_id' => Auth::id()]);
        $this->save($request, $item);

        return redirect()->route('admin.news')->with('success', __('The news item has been saved.'));
    }

    public function edit(NewsItem $newsItem): View
    {
        abort_if($newsItem->isAutomatic(), 404);

        return view('admin.news.form', ['item' => $newsItem]);
    }

    public function update(Request $request, NewsItem $newsItem): RedirectResponse
    {
        abort_if($newsItem->isAutomatic(), 404);
        $this->save($request, $newsItem);

        return redirect()->route('admin.news')->with('success', __('The news item has been saved.'));
    }

    public function destroy(NewsItem $newsItem): RedirectResponse
    {
        // An automatic item would be made again the next night: hide it instead.
        abort_if($newsItem->isAutomatic(), 404);
        $newsItem->delete();

        return redirect()->route('admin.news')->with('success', __('The news item has been deleted.'));
    }

    public function toggleHidden(NewsItem $newsItem): RedirectResponse
    {
        $newsItem->forceFill(['hidden' => ! $newsItem->hidden])->save();

        return back();
    }

    private function save(Request $request, NewsItem $item): void
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'body'         => ['required', 'string', 'max:20000'],
            'language'     => ['required', Rule::in(array_values(config('app.available_locales')))],
            'published_at' => ['required', 'date'],
            'expires_at'   => ['nullable', 'date', 'after:published_at'],
        ]);

        $item->fill($validated);
        $changed = ! $item->exists || $item->isDirty(['title', 'body', 'language']);
        if ($changed) {
            // The old translations no longer match the text.
            $item->translations = null;
        }
        $item->save();

        if ($changed && config('app.translate_observations')) {
            TranslateNewsItem::dispatch($item->id);
        }
    }
}
