<?php

namespace SimpleBus\Event\Bus;

use SimpleBus\Event\Event;

interface EventBus
{
    /**
     * @return void
     */
    public function handle(Event $event);
}
