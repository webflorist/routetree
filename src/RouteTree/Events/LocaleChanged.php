<?php

namespace Webflorist\RouteTree\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class LocaleChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var string
     */
    public $newLocale;

    /**
     * @var string|null
     */
    public $oldLocale;

    /**
     * Create a new event instance.
     *
     * @param string $newLocale
     * @param string|null $oldLocale
     */
    public function __construct(string $newLocale, ?string $oldLocale)
    {
        $this->newLocale = $newLocale;
        $this->oldLocale = $oldLocale;
    }
}
