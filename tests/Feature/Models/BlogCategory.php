<?php

namespace RouteTreeTests\Feature\Models;

use Illuminate\Database\Eloquent\Model;
use Webflorist\RouteTree\Interfaces\TranslatableRouteKey;

class BlogCategory extends Model implements TranslatableRouteKey
{

    public static function getAllRouteKeys(string $locale = null, ?array $parameters = null)
    {
        return self::getTestRouteKeys()[$locale];
    }

    public static function translateRouteKey(string $value, string $toLocale, string $fromLocale)
    {
        $values = self::getTestRouteKeys();
        return $values[$toLocale][array_search($value, $values[$fromLocale])];
    }

    /**
     * @return array
     */
    protected static function getTestRouteKeys(): array
    {
        return [
            'de' => [
                'blumen',
                'baeume'
            ],
            'en' => [
                'flowers',
                'trees'
            ]
        ];
    }
}