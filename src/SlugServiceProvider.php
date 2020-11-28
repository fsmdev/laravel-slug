<?php

namespace Fsmdev\LaravelSlug;

use Fsmdev\LaravelSlug\Facades\Slug as SlugFacade;
use Fsmdev\LaravelSlug\Models\Slug as SlugModel;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
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
            $class = config('laravel_slug.facade_class');
            return new $class;
        });

        $this->mergeConfigFrom(
            __DIR__.DIRECTORY_SEPARATOR.'config/laravel_slug.php', 'laravel_slug'
        );
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

        # php artisan vendor:publish --provider="Fsmdev\LaravelSlug\SlugServiceProvider" --tag=config
        $this->publishes([
            __DIR__.DIRECTORY_SEPARATOR.'config/laravel_slug.php' => config_path('laravel_slug.php'),
        ], 'config');

        Validator::extend('unique_slug', function($attribute, $value, $parameters)
        {
            $type = isset($parameters[0]) ? $parameters[0] : null;
            $id = isset($parameters[1]) ? $parameters[1] : null;

            $query = SlugModel::query()
                ->where('value', $value)
                ->where('entity_type', $type);

            if ($id) {
                $query->where('entity_id', '<>', $id);
            }

            return !$query->count();
        });
    }
}
