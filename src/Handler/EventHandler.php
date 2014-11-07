<?php

namespace SimpleBus\Event\Handler;

use SimpleBus\Event\Event;

interface EventHandler
{
    /**
     * @return void
     */
    public function handle(Event $event);
}
