<?php

// Quick smoke script to render the user-sessions view with sample data and print HTML
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

// Bootstrap the application so service providers (including view) are registered
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Instantiate the view factory
$viewFactory = $app->make('view');

use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

// Simple view-model stub that mimics the Eloquent ObservationSession used in views
class SessionViewStub
{
    public $id;
    public $name;
    public $observerid;
    public $preview;
    public $begindate;
    public $enddate;
    public $comments;
    public $observer;

    public function __construct($attrs = [])
    {
        foreach ($attrs as $k => $v) {
            $this->$k = $v;
        }
        $this->observer = (object) ['slug' => $attrs['observer_slug'] ?? null];
    }

    public function otherObserversCount()
    {
        return 1;
    }
}

// Create a fake page owner user (u) and viewer (user)
$u = (object) ['name' => 'Test User', 'slug' => 'test-user', 'username' => 'tester'];
$user = $u; // viewing own page
$userSlug = $u->slug;

// Build an active session and a draft session as simple objects
$active = new SessionViewStub([
    'id' => 101,
    'name' => 'Active Session Example',
    'observerid' => $u->username,
    'preview' => null,
    'begindate' => '2025-09-01 00:00:00',
    'enddate' => '2025-09-01 23:59:59',
    'comments' => 'Active session comments',
    'observer_slug' => $u->slug,
]);


// Create multiple drafts to simulate more than 10 drafts
$drafts = [];
for ($i = 1; $i <= 12; $i++) {
    $day = sprintf('%02d', ($i % 28) + 1);
    $drafts[] = new SessionViewStub([
        'id' => 200 + $i,
        'name' => 'Draft Session ' . $i,
        'observerid' => $u->username,
        'preview' => null,
        'begindate' => "2025-09-{$day} 00:00:00",
        'enddate' => "2025-09-{$day} 23:59:59",
        'comments' => 'Draft session comments ' . $i,
        'observer_slug' => $u->slug,
    ]);
}

// Create a LengthAwarePaginator for $sessions (active sessions)
$collection = new Collection([$active]);
$sessions = new LengthAwarePaginator($collection, 1, 12, 1, ['path' => '/sessions/'.$userSlug]);

// inactiveSessions as a Collection (more than 10)
$inactiveSessions = new Collection($drafts);

// Render the view
try {
    $html = $viewFactory->make('session.user-sessions', [
        'sessions' => $sessions,
        'u' => $u,
        'userSlug' => $userSlug,
        'user' => $user,
        'inactiveSessions' => $inactiveSessions,
    ])->render();

    // Print a trimmed snippet around the 'Draft sessions' heading to verify output
    $pos = strpos($html, 'Draft sessions');
    if ($pos !== false) {
        $snippet = substr($html, max(0, $pos - 200), 800);
        echo "--- FOUND 'Draft sessions' SNIPPET ---\n";
        echo $snippet . "\n";
    } else {
        echo "Draft sessions heading not found in rendered HTML.\nFull HTML:\n";
        echo substr($html, 0, 2000) . "\n";
    }
} catch (\Throwable $e) {
    echo "Error rendering view: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
