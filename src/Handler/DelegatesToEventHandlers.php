<?php

namespace SimpleBus\Event\Handler;

use SimpleBus\Event\Event;
use SimpleBus\Event\Bus\EventBus;
use SimpleBus\Event\Bus\RemembersNext;

class DelegatesToEventHandlers implements EventBus
{
    use RemembersNext;

    private $eventHandlersResolver;

    public function __construct(EventHandlersResolver $eventHandlersResolver)
    {
        $this->eventHandlersResolver = $eventHandlersResolver;
    }

    public function handle(Event $event)
    {
        $eventHandlers = $this->eventHandlersResolver->resolve($event);

        array_walk(
            $eventHandlers,
            function (EventHandler $eventHandler) use ($event) {
                $eventHandler->handle($event);
            }
        );
    }
}
