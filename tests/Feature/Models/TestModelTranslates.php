<?php

namespace RouteTreeTests\Feature\Models;

use Illuminate\Database\Eloquent\Model;
use Webflorist\RouteTree\Interfaces\ProvidesRouteKeyList;
use Webflorist\RouteTree\Interfaces\ProvidesRoutePayload;
use Webflorist\RouteTree\Interfaces\TranslatesRouteKey;

class TestModelTranslates extends Model implements ProvidesRouteKeyList, ProvidesRoutePayload, TranslatesRouteKey
{

    public static function getRouteKeyList(string $locale = null, ?array $parameters = null): array
    {
        return $locale === 'de' ? ['wert-1', 'wert-2'] : ['value-1', 'value-2'];
    }

    public static function translateRouteKey(string $value, string $toLocale, string $fromLocale): string
    {
        $values = [
            'de' => [
                'test-model-wert1',
                'test-model-wert2'
            ],
            'en' => [
                'test-model-value1',
                'test-model-value2'
            ]
        ];
        return $values[$toLocale][array_search($value, $values[$fromLocale])];
    }

    public static function getRoutePayload(string $payloadKey, array $parameters, string $locale, ?string $action)
    {
        // TODO: Implement getRoutePayload() method.
    }
}