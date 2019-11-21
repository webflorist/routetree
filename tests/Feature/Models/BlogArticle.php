<?php

namespace RouteTreeTests\Feature\Models;

use Illuminate\Database\Eloquent\Model;
use Webflorist\RouteTree\Interfaces\TranslatableRouteKey;

class BlogArticle extends Model implements TranslatableRouteKey
{

    public static function getAllRouteKeys(string $locale = null, ?array $parameters = null): ?array
    {
        if (isset($parameters['category'])) {
            return self::getTestRouteKeys($parameters['category'])[$locale];
        }
        return [];
    }

    public static function translateRouteKey(string $value, string $toLocale, string $fromLocale): string
    {
        $values = self::getTestRouteKeys();
        return $values[$toLocale][array_search($value, $values[$fromLocale])];
    }

    public static function getRoutePayload(string $payloadKey, array $parameters, string $locale, ?string $action)
    {
        return null;
    }

    /**
     * @return array
     */
    protected static function getTestRouteKeys($category): array
    {
        $routeKeys = [
            'blumen' => [
                'de' => [
                    'die-rose',
                    'die-tulpe',
                    'die-lilie'
                ],
                'en' => [
                    'the-rose',
                    'the-tulip',
                    'the-lily'
                ]
            ],
            'baeume' => [
                'de' => [
                    'die-laerche',
                    'die-laerche',
                    'die-kastanie'
                ],
                'en' => [
                    'the-larch',
                    'the-larch',
                    'the-chestnut'
                ]
            ]
        ];
        $routeKeys['flowers'] = $routeKeys['blumen'];
        $routeKeys['trees'] = $routeKeys['baeume'];
        return $routeKeys[$category];
    }
}