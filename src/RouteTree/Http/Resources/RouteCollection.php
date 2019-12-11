<?php

namespace Webflorist\RouteTree\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class RouteCollection extends ResourceCollection
{

    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection
        ];
    }
}