<?php

namespace RouteTreeTests\Feature\Models;

use Illuminate\Database\Eloquent\Model;
use Webflorist\RouteTree\Interfaces\ProvidesRouteKeyList;
use Webflorist\RouteTree\Interfaces\ProvidesRoutePayload;
use Webflorist\RouteTree\Interfaces\TranslatesRouteKey;

class BlogArticle extends Model implements ProvidesRouteKeyList, ProvidesRoutePayload, TranslatesRouteKey
{

    public static function getRouteKeyList(string $locale = null, ?array $parameters = null): array
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
        switch ($payloadKey) {
            case 'title':
            case 'navTitle':
                if (isset($parameters['category']) && isset($parameters['article'])) {
                    return self::getArticleTitle($parameters['category'], $parameters['article'], $locale);
                }
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