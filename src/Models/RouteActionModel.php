<?php

namespace Webflorist\RouteTree\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property int node_id
 * @property string name
 * @property string type
 * @property string value
 * @property array|null middleware
 */
class RouteActionModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'route_actions';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'middleware' => 'array'
    ];

    /**
     * Get middleware and make sure to return array.
     *
     * @param  mixed $value
     * @return array
     */
    public function getMiddlewareAttribute($value)
    {
        if (is_null($value)) {
            return [];
        }
        return json_decode($value, true);
    }

    /**
     * Get parent.
     */
    public function node()
    {
        return $this->belongsTo(RouteNodeModel::class, 'node_id');
    }
}
