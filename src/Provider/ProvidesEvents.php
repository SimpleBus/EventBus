<?php

namespace SimpleBus\Event\Provider;

use SimpleBus\Event\Event;

interface ProvidesEvents
{
    /**
     * @return Event[]
     */
    public function releaseEvents();
}
