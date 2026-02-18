<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $maintenanceMode = $this->isMaintenanceModeActive();

        if ($maintenanceMode && !$this->isAllowed($request) && !$this->isException($request)) {
            if ($this->isAuthRoute($request) && !Auth::check()) {
                return $next($request);
            }
            return response()->view('errors.503', [], 503);
        }

        return $next($request);
    }

    private function isMaintenanceModeActive(): bool
    {
        try {
            $setting = DB::table('settings')->where('key', 'maintenance_mode')->first();
            return $setting && $setting->value === '1';
        } catch (\Exception $e) {
            return false;
        }
    }

    private function isAllowed(Request $request): bool
    {
        return Auth::check() && Auth::user()->isAdminPanel();
    }

    private function isException(Request $request): bool
    {
        $exceptions = [
            'logout',
            'login',
            'password.request',
            'password.email',
            'password.reset',
            'register',
        ];

        $routeName = $request->route()?->getName();
        
        return in_array($routeName, $exceptions);
    }

    private function isAuthRoute(Request $request): bool
    {
        $authRoutes = [
            'login',
            'logout',
            'register',
            'password.request',
            'password.email',
            'password.reset',
            'password.store',
        ];

        $routeName = $request->route()?->getName();
        
        return in_array($routeName, $authRoutes);
    }
}
