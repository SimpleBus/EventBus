<?php

namespace SimpleBus\Event\Bus;

use SimpleBus\Event\Event;

class FinishesEventBeforeHandlingNext implements EventBus
{
    use RemembersNext;

    private $queue = array();
    private $isHandling = false;

    public function handle(Event $event)
    {
        $this->queue[] = $event;

        if (!$this->isHandling) {
            $this->isHandling = true;

            while ($event = array_shift($this->queue)) {
                $this->next($event);
            }

            $this->isHandling = false;
        }
    }
}
