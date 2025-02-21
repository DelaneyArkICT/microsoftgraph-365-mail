<?php

namespace LLoadout\Microsoftgraph;

use LLoadout\Microsoftgraph\Providers\EventServiceProvider;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MicrosoftgraphServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('microsoftgraph');

        $this->buildRoutes();
        $this->buildConfigs();
        $this->buildFacades();

        $this->app->register(EventServiceProvider::class);

    }

    private function buildRoutes()
    {
        $this->app['router']->get('microsoft/connect', [
            'uses' => '\LLoadout\Microsoftgraph\Authenticate@connect',
            'as' => 'graph.connect',
        ])->middleware('web');

        $this->app['router']->get('microsoft/callback', [
            'uses' => '\LLoadout\Microsoftgraph\Authenticate@callback',
            'as' => 'graph.callback',
        ])->middleware('web');
    }

    private function buildConfigs()
    {
        $config = $this->app['config']->get('services', []);
        $this->app['config']->set('services', array_merge([
            'microsoft' => [
                'tenant' => env('MS_TENANT_ID'),
                'client_id' => env('MS_CLIENT_ID'),
                'client_secret' => env('MS_CLIENT_SECRET'),
                'redirect' => env('MS_REDIRECT_URL'),
                'redirect_after_callback' => env('MS_REDIRECT_AFTER_CALLBACK_URL','/'),
            ],
        ], $config));

        $config = $this->app['config']->get('mail', []);
        $this->app['config']->set('mail.mailers', array_merge([
            'microsoftgraph' => [
                'transport' => 'microsoftgraph',
            ],
        ], $config['mailers']));

        $config = $this->app['config']->get('filesystems.disks', []);
        $this->app['config']->set('filesystems.disks', array_merge([
            'onedrive' => [
                'driver' => 'onedrive',
                'root' => env('MS_ONEDRIVE_ROOT'),
            ],
        ], $config));

    }

    private function buildFacades()
    {

        $this->app->bind('teams', function ($app) {
            return new \LLoadout\Microsoftgraph\Teams();
        });

        $this->app->bind('excel', function ($app) {
            return new \LLoadout\Microsoftgraph\Excel();
        });
    }
}
