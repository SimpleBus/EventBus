<?php

namespace SimpleBus\Event;

interface Event
{
    /**
     * @return string
     */
    public function name();
}
