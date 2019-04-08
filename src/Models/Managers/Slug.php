<?php

namespace Fsmdev\LaravelSlug\Models\Managers;

use Closure;
use Fsmdev\LaravelSlug\Models\Slug as SlugModel;
use Illuminate\Database\Eloquent\Model;

class Slug
{
    /**
     * @param string $value
     * @param Closure $getUrl
     * @param string|null $type
     * @return Model
     */
    public function findOrRedirect($value, Closure $getUrl, $type = null)
    {
        $slug = $this->findSlug($value, $type);

        if (!$slug) {
            abort(404);
        }

        if ($slug->redirect) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " . $getUrl($slug->entity));
            exit;
        }

        return $slug->entity;
    }

    /**
     * @param string $value
     * @param string|null $type
     * @return Model
     */
    public function findOrFail($value, $type = null)
    {
        $slug = $this->findSlug($value, $type);
        if (!$slug) {
            abort(404);
        }
        return $slug->entity;
    }

    /**
     * @param string $value
     * @param string|null $type
     * @return Model|null
     */
    public function find($value, $type = null)
    {
        $slug = $this->findSlug($value, $type);
        return $slug ? $slug->entity : null;
    }

    /**
     * @param string $value
     * @param string|null $type
     * @return null|SlugModel
     */
    protected function findSlug($value, $type = null)
    {
        $query = SlugModel::where('value', $value);
        if ($type) {
             $query->where('entity_type', $type);
        }

        return $query->first();
    }
}