<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Events;
use Stancl\Tenancy\Jobs;
use Stancl\Tenancy\Middleware;

class TenancyServiceProvider extends ServiceProvider
{
    public static string $controllerNamespace = '';

    public function events(): array
    {
        return [
            Events\TenantCreated::class => [
                Jobs\CreateDatabase::class,
                Jobs\MigrateDatabase::class,
            ],

            Events\TenantDeleted::class => [
                Jobs\DeleteDatabase::class,
            ],

            Events\TenancyInitialized::class => [],
            Events\TenancyEnded::class => [],
            Events\DatabaseMigrated::class => [],
            Events\DatabaseSeeded::class => [],
            Events\InitializingTenancy::class => [],
            Events\EndingTenancy::class => [],
            Events\BootstrappingTenancy::class => [],
            Events\TenancyBootstrapped::class => [],
            Events\RevertingToCentralContext::class => [],
            Events\RevertedToCentralContext::class => [],
        ];
    }

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->bootEvents();
        $this->mapRoutes();
        $this->makeTenancyMiddlewareHighestPriority();
    }

    protected function bootEvents(): void
    {
        foreach ($this->events() as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }
    }

    protected function mapRoutes(): void
    {
        $this->mapTenantRoutes();
        $this->mapCentralRoutes();
    }

    protected function mapTenantRoutes(): void
    {
        if (! file_exists(base_path('routes/tenant.php'))) {
            return;
        }

        Route::middleware([
            'web',
            Middleware\InitializeTenancyByDomain::class,
            Middleware\PreventAccessFromCentralDomains::class,
        ])->group(base_path('routes/tenant.php'));
    }

    protected function mapCentralRoutes(): void
    {
        if (! file_exists(base_path('routes/central.php'))) {
            return;
        }

        foreach (config('tenancy.central_domains', []) as $domain) {
            Route::middleware('web')
                ->domain($domain)
                ->group(base_path('routes/central.php'));
        }
    }

    protected function makeTenancyMiddlewareHighestPriority(): void
    {
        $middleware = [
            Middleware\PreventAccessFromCentralDomains::class,
            Middleware\InitializeTenancyByDomain::class,
            Middleware\InitializeTenancyBySubdomain::class,
            Middleware\InitializeTenancyByDomainOrSubdomain::class,
            Middleware\InitializeTenancyByPath::class,
            Middleware\InitializeTenancyByRequestData::class,
        ];

        foreach (array_reverse($middleware) as $middlewareClass) {
            $this->app[Kernel::class]->prependToMiddlewarePriority($middlewareClass);
        }
    }
}