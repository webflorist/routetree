<?php

namespace RouteTreeTests\Feature\Models;

use Illuminate\Database\Eloquent\Model;
use Webflorist\RouteTree\Interfaces\RouteParameterModelContract;

class TestModelContract extends Model implements RouteParameterModelContract
{

    public static function getRouteParameterValues(string $locale = null, ?array $parameters = null)
    {
        return $locale === 'de' ? ['wert-1','wert-2'] : ['value-1','value-2'];
    }
}