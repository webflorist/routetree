<?php

namespace Webflorist\RouteTree\Domain;

/**
 * Class LanguageMapping
 *
 * This class is essentially a multi-lingual value-object.
 * Allowing to store/retrieve values for different locales.
 *
 * @package Webflorist\RouteTree
 */
class LanguageMapping
{

    /**
     * Array of values
     * using the locale as key.
     *
     * @var array
     */
    protected $values = [];

    /**
     * Create a new LanguageMapping object.
     *
     * @param array|null $languageMapping
     * @return LanguageMapping
     */
    public static function create(?array $languageMapping = null): LanguageMapping
    {
        $instance = new self();
        if (is_array($languageMapping)) {
            foreach ($languageMapping as $locale => $value) {
                $instance->set($locale, $value);
            }
        }
        return $instance;
    }

    /**
     * Sets $value for $locale.
     *
     * @param string $locale
     * @param mixed $value
     * @return $this
     */
    public function set(string $locale, $value): LanguageMapping
    {
        $this->values[$locale] = $value;
        return $this;
    }

    /**
     * Retrieves the value set for $locale.
     *
     * @param string $locale
     * @return mixed
     */
    public function get(string $locale)
    {
        return $this->values[$locale];
    }

    /**
     * Does this LanguageMapping
     * contain a value for $locale?
     *
     * @param string $locale
     * @return bool
     */
    public function has(string $locale): bool
    {
        return isset($this->values[$locale]);
    }


}