<?php

namespace Fsmdev\LaravelSlug\Models\Traits;

use Fsmdev\LaravelSlug\Models\Slug;

trait HasSlug
{
    protected $slugRead = false;
    protected $slug;
    protected $redirects = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function slugs()
    {
        return $this->morphMany('Fsmdev\LaravelSlug\Models\Slug', 'entity');
    }

    /**
     * @param $value
     */
    public function setSlugAttribute($value)
    {
        $this->slugRead();
        $this->slug = $this->stringToSlug($value);
    }

    /**
     * @return null|string
     */
    public function getSlugAttribute()
    {
        $this->slugRead();
        return $this->slug;
    }

    /**
     * @return array
     */
    public function redirects()
    {
        $this->slugRead();
        return $this->redirects;
    }

    /**
     * @param string $value
     */
    public function redirectAdd($value)
    {
        $this->slugRead();

        $value = $this->stringToSlug($value);

        if (!in_array($value, $this->redirects, true)) {
            $this->redirects[] = $value;
        }
    }

    /**
     * @param array $redirects
     */
    public function redirectSet($redirects)
    {
        $this->slugRead();
        $this->redirects = $redirects;
    }

    /**
     * @param $value
     */
    public function redirectDelete($value)
    {
        $this->slugRead();

        $value = $this->stringToSlug($value);

        $index = array_search($value, $this->redirects, true);
        if ($index !== false) {
            unset($this->redirects[$index]);
            $this->redirects = array_values($this->redirects);
        }
    }

    /**
     * @return void
     */
    public function redirectClear()
    {
        $this->slugRead();
        $this->redirects = [];
    }

    /**
     * @return null|array
     */
    protected function slugFields()
    {
        return null;
    }

    /**
     * @return null|string
     */
    protected function slugGenerate()
    {
        $fields = $this->slugFields();
        if (!is_array($fields)) {
            return null;
        }

        $parts = [];
        foreach ($fields as $field) {
            $parts[] = $this->stringToSlug($this->$field);
        }

        return implode('-', $parts);
    }

    /**
     * @param string $string
     * @return string
     */
    protected function stringToSlug($string)
    {
        $string = preg_replace("/[^а-яА-Яa-zA-Z0-9\-_ ]/u", "", $string);
        $string = trim(mb_strtolower($string));

        $string = strtr($string, [
            ' ' => '-',
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'e',
            'з' => 'z',
            'и' => 'i',
            'й' => 'j',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'c',
            'ы' => 'y',
            'э' => 'e',
            'щ' => 'shch',
            'ш' => 'sh',
            'ъ' => '',
            'ь' => '',
            'ю' => 'yu',
            'я' => 'ya',
            'ж' => 'zh',
            'ч' => 'ch'
        ]);

        return $string;
    }

    /**
     * @return void
     */
    public function slugRead()
    {
        if ($this->slugRead) {
            return;
        }

        $this->slugRead = true;

        # Main
        $slug = $this->slugs()->notRedirect()->first();
        $this->slug = $slug instanceof Slug ? $slug->value : null;

        # Redirects
        $this->redirects = $this->redirectsArrayRead();
    }

    /**
     * @throws \Exception
     */
    public function slugWrite()
    {
        $this->slugRead();

        # Main
        $this->slug = $this->slug ? $this->slug : $this->slugGenerate();

        $slug = $this->slugs()->notRedirect()->first();

        if ($this->slug) {
            if (!$slug instanceof Slug) {
                $slug = $this->slugs()->make();
            }
            $slug->value = $this->slug;
            $slug->redirect = false;
            $slug->save();

        } else {
            if ($slug instanceof Slug) {
                $slug->delete();
            }
        }

        # Redirects
        $current = $this->redirectsArrayRead();
        foreach ($this->redirects as $redirect) {
            $index = array_search($redirect, $current, true);
            if ($index !== false) {
                unset($current[$index]);
                $current = array_values($current);
                continue;
            }

            $slug = $this->slugs()->make();
            $slug->value = $redirect;
            $slug->redirect = true;
            $slug->save();
        }

        # Remove current redirects that not in actual array
        if ($current) {
            $this->slugs()->redirect()->whereIn('value', $current)->delete();
        }
    }

    /**
     * @return void
     */
    public static function bootHasSlug()
    {
        static::saved(function ($model) {
            $model->slugWrite();
        });

        static::deleted(function ($model) {
            $model->slugs()->delete();
        });
    }

    /**
     * @return array
     */
    protected function redirectsArrayRead()
    {
        $slugs = $this->slugs()->redirect()->get();
        $redirects = [];
        foreach ($slugs as $slug) {
            $redirects[] = $slug->value;
        }
        return $redirects;
    }
}