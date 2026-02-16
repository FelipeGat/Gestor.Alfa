<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiVersion
{
    private const VERSIONS = ['v1'];

    private const DEFAULT_VERSION = 'v1';

    public function handle(Request $request, Closure $next): Response
    {
        $version = $this->extractVersion($request);

        if ($version && ! in_array($version, self::VERSIONS)) {
            return response()->json([
                'success' => false,
                'message' => "API version '{$version}' not supported. Supported versions: ".implode(', ', self::VERSIONS),
            ], 400);
        }

        $request->attributes->set('api_version', $version ?? self::DEFAULT_VERSION);

        return $next($request);
    }

    private function extractVersion(Request $request): ?string
    {
        if ($request->header('Accept')) {
            $acceptHeader = $request->header('Accept');

            if (preg_match('/version=(\w+)/', $acceptHeader, $matches)) {
                return $matches[1];
            }
        }

        if ($request->route('version')) {
            return $request->route('version');
        }

        $path = $request->path();
        if (preg_match('/^(v\d+)/', $path, $matches)) {
            return $matches[1];
        }

        return self::DEFAULT_VERSION;
    }
}
