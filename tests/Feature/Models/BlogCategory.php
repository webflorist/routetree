<?php

namespace RouteTreeTests\Feature\Models;

use Illuminate\Database\Eloquent\Model;
use Webflorist\RouteTree\Interfaces\ProvidesRouteKeyList;
use Webflorist\RouteTree\Interfaces\ProvidesRoutePayload;
use Webflorist\RouteTree\Interfaces\TranslatesRouteKey;

class BlogCategory extends Model implements ProvidesRouteKeyList, ProvidesRoutePayload, TranslatesRouteKey
{

    public static function getRouteKeyList(string $locale = null, ?array $parameters = null): ?array
    {
        return self::getTestRouteKeys()[$locale];
    }

    public static function translateRouteKey(string $value, string $toLocale, string $fromLocale): string
    {
        $values = self::getTestRouteKeys();
        return $values[$toLocale][array_search($value, $values[$fromLocale])];
    }

    public static function getRoutePayload(string $payloadKey, array $parameters, string $locale, ?string $action)
    {
        switch ($payloadKey) {
            case 'title':
            case 'navTitle':
                if (isset($parameters['category'])) {
                    return self::getCategoryTitle($parameters['category'], $locale);
                }
        }
        return null;
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

    protected static function getCategoryTitle($routeKey, $locale)
    {
        $titles = [
            'de' => [
                'blumen' => 'Artikel über Blumen',
                'baeume' => 'Artikel über Bäume'
            ],
            'en' => [
                'flowers' => 'Articles about flowers',
                'trees' => 'Articles about trees'
            ]
        ];
        if (isset($titles[$locale][$routeKey])) {
            return $titles[$locale][$routeKey];
        }
    }
}