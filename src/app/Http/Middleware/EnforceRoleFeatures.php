<?php

namespace App\Http\Middleware;

use App\Support\Access\RoleFeatureRegistry;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menolak halaman resource yang bukan fitur role login, walaupun URL-nya dibuka langsung.
 */
class EnforceRoleFeatures
{
    public function handle(Request $request, Closure $next): Response
    {
        $panel = Filament::getCurrentPanel();
        $route = (string) $request->route()?->getName();
        $user = $request->user();

        if (! $panel || ! $user || ! str_starts_with($route, "filament.{$panel->getId()}.resources.")) {
            return $next($request);
        }

        $resource = collect($panel->getResources())
            ->first(fn (string $resource) => str_starts_with($route, $resource::getRouteBaseName($panel->getId()) . '.'));

        $allowed = RoleFeatureRegistry::allowedResources($user->getRoleNames()->all());

        abort_if($resource && ! in_array($resource, $allowed, true), 403, 'Fitur ini bukan bagian dari hak akses role Anda.');

        return $next($request);
    }
}
