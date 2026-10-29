{{-- The documentation of the token API /api/v1/me: Swagger UI on the OpenAPI description of App\Support\Api\MeApiSpec --}}
<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>DeepskyLog API</title>
        <link rel="icon" href="{{ asset("favicon.ico") }}" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.17.14/swagger-ui.css" />
        <style>
            body { margin: 0; background: #fff; font-family: sans-serif; }
            .dsl-header { display: flex; flex-wrap: wrap; gap: 1rem; align-items: baseline; justify-content: space-between; padding: 0.75rem 1rem; background: #111827; color: #e5e7eb; }
            .dsl-header a { color: #93c5fd; }
            .dsl-header p { margin: 0; font-size: 0.9rem; }
        </style>
    </head>
    <body>
        <div class="dsl-header">
            <p><a href="{{ url("/") }}">DeepskyLog</a> &middot; {{ __("API documentation") }}</p>
            <p>
                <a href="{{ route("api.openapi") }}">openapi.json</a>
                @auth
                    &middot; <a href="{{ url("/user/api-tokens") }}">{{ __("API Tokens") }}</a>
                @endauth
            </p>
        </div>
        <div id="swagger-ui"></div>
        <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.17.14/swagger-ui-bundle.js"></script>
        <script>
            window.ui = SwaggerUIBundle({
                url: @json(route("api.openapi")),
                dom_id: "#swagger-ui",
                deepLinking: true,
                persistAuthorization: false,
            });
        </script>
    </body>
</html>
