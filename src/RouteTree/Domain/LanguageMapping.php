<?php

namespace Webflorist\RouteTree\Domain;

class LanguageMapping
{

    protected $values = [];

    public static function create(?array $languageMapping = null)
    {
        $instance = new self();
        if (is_array($languageMapping)) {
            foreach ($languageMapping as $locale => $value) {
                $instance->set($locale, $value);
            }
        }
        return $instance;
    }

    public function set(string $locale, $value)
    {
        $this->values[$locale] = $value;
        return $this;
    }

    public function get(string $locale)
    {
        return $this->values[$locale];
    }

    public function has(string $locale)
    {
        return isset($this->values[$locale]);
    }


}