<?php

namespace SimpleBus\Event\Handler;

use SimpleBus\Event\Event;

interface EventHandlersResolver
{
    /**
     * @param Event $event
     * @return EventHandler[]
     * @throws \InvalidArgumentException
     */
    public function resolve(Event $event);
}
