<?php

namespace Webflorist\RouteTree\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class Redirected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var string
     */
    public $fromUri;

    /**
     * @var string
     */
    public $toUri;

    /**
     * Create a new event instance.
     *
     * @param string $fromUri
     * @param string $toUri
     */
    public function __construct(string $fromUri, string $toUri)
    {
        $this->fromUri = $fromUri;
        $this->toUri = $toUri;
    }
}
