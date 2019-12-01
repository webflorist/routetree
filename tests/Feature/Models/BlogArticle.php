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
        if ($payloadKey === 'title' && $action = 'show' && isset($parameters['category']) && isset($parameters['article'])) {
            self::getArticleTitle($parameters['category'], $parameters['article'], $locale);
        }
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


    protected static function getArticleTitle(string $category, string $article, string $locale): ?string
    {
        $de = [
            'blumen' => [
                'die-rose' => 'Die Rose - Blume im Wandel der Zeit',
                'die-tulpe' => 'Dit Tulpe im weltgeschichtlichen Finanzsystem',
                'die-lilie' => 'Sehet die Lilien!',
            ],
            'baeume' => [
                'die-laerche' => 'Und jetzt... Die Lärche',
                'die-kastanie' => 'Und jetzt... Der Kastanienbaum',
            ]
        ];
        if ($locale === 'de') {
            return $de[$category][$article] ?? null;
        }
        return null;
    }
}