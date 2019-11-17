<?php

namespace RouteTreeTests\Feature\Models;

use Illuminate\Database\Eloquent\Model;
use Webflorist\RouteTree\Interfaces\RouteParameterModelContract;

class TestModel extends Model implements RouteParameterModelContract
{

    public static function getRouteParameterValues(string $locale = null, ?array $parameters = null)
    {
        return $locale === 'de' ? ['wert-1','wert-2'] : ['value-1','value-2'];
    }

    public static function translateRouteParameterValue(string $value, string $toLocale, string $fromLocale)
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
}