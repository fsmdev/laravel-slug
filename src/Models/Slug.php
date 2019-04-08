<?php

namespace Fsmdev\LaravelSlug\Models;

use Illuminate\Database\Eloquent\Model;

class Slug extends Model
{
    protected $fillable = [
        'entity_id',
        'entity_type',
        'value',
        'redirect',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function entity()
    {
        return $this->morphTo();
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeRedirect($query)
    {
        return $query->where('redirect', 1);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeNotRedirect($query)
    {
        return $query->where(function ($query) {
            $query->where('redirect', 0)->orWhereNull('redirect');
        });
    }
}
