<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantBySubdomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $context = app(TenantContext::class);
        $context->clear();
        URL::defaults([]);

        if ($this->isLegacyCentralHost($request)) {
            return redirect()->to($request->getScheme() . '://' . config('sifak.base_domain') . $request->getRequestUri(), 308);
        }

        if (! Schema::hasTable('tenants')) {
            return $next($request);
        }

        $slug = $this->resolveSlug($request);

        if (! $slug) {
            return $next($request);
        }

        $tenant = Tenant::where('slug', $slug)->first();

        abort_if(! $tenant, 404, 'Tenant tidak ditemukan.');
        abort_if(! $tenant->isActive(), 403, 'Tenant sedang tidak aktif.');

        $context->set($tenant);
        URL::defaults(['tenant' => $tenant->slug]);
        $request->session()->put('tenant_slug', $tenant->slug);

        return $next($request);
    }

    private function resolveSlug(Request $request): ?string
    {
        $host = $request->getHost();
        $baseDomain = config('sifak.base_domain');

        if ($baseDomain && $host === $baseDomain) {
            $request->session()->forget('tenant_slug');

            return null;
        }

        if (app()->isLocal() && $request->query('tenant') === 'central') {
            $request->session()->forget('tenant_slug');

            return null;
        }

        if (app()->isLocal() && $request->query('tenant') && ! str_ends_with($host, (string) $baseDomain)) {
            return str($request->query('tenant'))->lower()->slug()->value();
        }

        if (app()->isLocal() && in_array($host, ['localhost', '127.0.0.1'], true) && $request->session()->has('tenant_slug')) {
            return $request->session()->get('tenant_slug');
        }

        if ($baseDomain && str_ends_with($host, '.' . $baseDomain)) {
            $slug = str($host)->before('.' . $baseDomain)->value();

            if (in_array($slug, ['admin', 'super-admin', 'www'], true)) {
                $request->session()->forget('tenant_slug');

                return null;
            }

            return $slug;
        }

        return null;
    }

    private function isLegacyCentralHost(Request $request): bool
    {
        $baseDomain = config('sifak.base_domain');

        return filled($baseDomain) && $request->getHost() === 'admin.' . $baseDomain;
    }
}
