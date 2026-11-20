<?php

use App\Mcp\Servers\DeepskyLogServer;
use Laravel\Mcp\Facades\Mcp;

/*
|--------------------------------------------------------------------------
| MCP server
|--------------------------------------------------------------------------
|
| Loaded by laravel/mcp's service provider, outside the web and api
| middleware groups (no session, no CSRF). AI assistants (Claude, ChatGPT,
| Cursor, ...) connect to /mcp with a personal access token of the observer
| (/user/api-tokens) as "Authorization: Bearer <token>"; the tools act for
| that observer (App\Mcp\Servers\DeepskyLogServer). The 'mcp' rate limiter
| is in RouteServiceProvider.
|
*/

Mcp::web('/mcp', DeepskyLogServer::class)
    ->middleware(['auth:sanctum', 'abilities:read', 'throttle:mcp', 'doNotCacheResponse'])
    ->name('mcp');
