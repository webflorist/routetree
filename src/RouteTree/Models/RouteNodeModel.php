<?php

namespace Webflorist\RouteTree\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property int parent_id
 * @property string name
 * @property array|null segments
 * @property array|null middleware
 * @property string|null namespace
 * @property boolean inherit_path
 * @property array|null data
 * @property RouteNodeModel|null parentNode
 */
class RouteNodeModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'route_nodes';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'segments' => 'array',
        'middleware' => 'array',
        'data' => 'array',
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
     * Does this node have a parent.
     */
    public function hasParentNode()
    {
        return !is_null($this->parent_id);
    }

    /**
     * Get the actions for the node.
     */
    public function actions()
    {
        return $this->hasMany(RouteActionModel::class, 'node_id');
    }

    /**
     * Get parent.
     */
    public function parentNode()
    {
        return $this->belongsTo(RouteNodeModel::class, 'parent_id');
    }

    public function getParentNodeId()
    {
        if ($this->hasParentNode()) {
            return $this->parentNode->resolveId();
        }
        return '';
    }

    /**
     * Resolves the Database interger-ID to
     * the string representation.
     *
     * @return string
     */
    public function resolveId()
    {
        if ($this->hasParentNode()) {
            return $this->parentNode->resolveId() . '.' . $this->name;
        }
        return $this->name;
    }

}
