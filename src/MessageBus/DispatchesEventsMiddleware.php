<?php

namespace SimpleBus\Event\MessageBus;

use SimpleBus\Message\Bus\MessageBus;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;
use SimpleBus\Event\Provider\ProvidesEvents;
use SimpleBus\Message\Message;

class DispatchesEventsMiddleware implements MessageBusMiddleware
{
    /**
     * @var ProvidesEvents
     */
    private $eventProvider;

    /**
     * @var MessageBus
     */
    private $eventBus;

    public function __construct(ProvidesEvents $eventProvider, MessageBus $eventBus)
    {
        $this->eventProvider = $eventProvider;
        $this->eventBus = $eventBus;
    }

    public function handle(Message $message, callable $next)
    {
        $next($message);

        foreach ($this->eventProvider->releaseEvents() as $event) {
            $this->eventBus->handle($event);
        }
    }
}
