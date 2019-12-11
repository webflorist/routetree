<?php

namespace Webflorist\RouteTree\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class NodeNotFound
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * ID of the RouteNode, that could not be found.
     * Is null, if no current route was found.
     *
     * @var string|null
     */
    public $nodeId;

    /**
     * Create a new event instance.
     *
     * @param string $nodeId
     */
    public function __construct(?string $nodeId)
    {
        $this->nodeId = $nodeId;
    }
}
