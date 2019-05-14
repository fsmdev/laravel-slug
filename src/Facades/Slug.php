<?php

namespace Fsmdev\LaravelSlug\Facades;

use Closure;
use Illuminate\Support\Facades\Facade;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Slug
 * @package Fsmdev\LaravelSlug\Facades
 *
 * @method static Model findOrRedirect(string $value, Closure $getUrl, string|null $type = null)
 * @method static Model findOrFail(string $value, string|null $type = null)
 * @method static null|Model find(string $value, string|null $type = null)
 */
class Slug extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor() {
        return 'fsmdev_laravel_slug_manager';
    }
}