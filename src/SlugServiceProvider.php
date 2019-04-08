<?php

namespace Fsmdev\LaravelSlug;

use Fsmdev\LaravelSlug\Models\Managers\Slug;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class SlugServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        App::bind('fsmdev_laravel_slug_manager', function()
        {
            return new Slug();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        # php artisan vendor:publish --provider="Fsmdev\LaravelSlug\SlugServiceProvider" --tag=migration
        $this->publishes([__DIR__.DIRECTORY_SEPARATOR.'migrations/2019_03_12_050059_create_slugs_table.stub'
            => database_path('migrations/'.date('Y_m_d_His').'_create_slugs_table.php'),
        ], 'migration');
    }
}
