# EventBus

[![Build Status](https://travis-ci.org/SimleBus/EventBus.svg?branch=master)](https://travis-ci.org/SimpleBus/EventBus)

By [Matthias Noback](http://php-and-symfony.matthiasnoback.nl/)

## Installation

Using Composer:

    composer require simple-bus/event-bus

## Usage

1. Create an event

    ```php
    use SimpleBus\Event\Event;

    class UserRegisteredEvent implements Event
    {
        const NAME = 'user_registered';

        public function name()
        {
            return self::NAME;
        }
    }
    ```

2. Create an event handler

    ```php
    use SimpleBus\Event\Handler\EventHandler;

    class SendConfirmationMailWhenUserRegistered implements EventHandler
    {
        public function handle(Event $event)
        {
            ...
        }
    }
    ```

3. Set up the event bus and the event handler resolver:

    ```php
    use SimpleBus\Event\Bus\DelegatesToEventHandlers;
    use SimpleBus\Event\Handler\LazyLoadingEventHandlersResolver;

    $eventHandlersResolver = new LazyLoadingEventHandlersResolver(
        function ($serviceId) {
            // lazily load/create instances of the given event handler service, e.g. using a service locator
             $handler = ...;

             return $handler;
        },
        array(
            UserRegisteredEvent::NAME => array(
                'send_confirmation_mail_when_user_registered_service_id',
                // add other handler service ids
                ...
            )
        )
    );

    $eventBus = new DelegatesToEventHandlers($eventHandlersResolver);

    $userRegisteredEvent = new UserRegisteredEvent();

    $eventBus->handle($userRegisteredEvent);
    ```

Because an event handler might call the event bus to handle new events, it's better to wrap the event bus to make sure
that the first event is fully handled first:

```php
use SimpleBus\Event\Bus\FinishesEventBeforeHandlingNext;

$eventBusWrapper = new FinishesEventBeforeHandlingNext();
$eventBusWrapper->setNext($eventBus);

$eventBusWrapper->handle($userRegisteredEvent);
```

### Event providers

Because it is very likely that in your application events originate from other objects that collect and later provide
events, this library contains a set of basic classes and interfaces for those types of event providers too.

```php
use SimpleBus\Event\Provider\ProvidesEvents;
use SimpleBus\Event\Provider\EventProviderCapabilities;

class User implements ProvidesEvents
{
    use EventProviderCapabilities;

    public static function register($email)
    {
        return new self($email);
    }

    private function __construct($email)
    {
        $this->raise(new UserRegisteredEvent());
    }
}
```

Afterwards you can collect events from the entity:

```php
$user = User::register('matthiasnoback@gmail.com');

// $events will be an array containing an object of type UserRegisteredEvent
$events = $user->releaseEvents();
```

## Extension points

### Specialized event buses

You can add your own specialized event bus implementations. You can chain them using `EventBus::setNext()`.

If your event bus needs to call the next event bus in the chain, use the `RemembersNext` trait to prevent some code
duplication:

```php
use SimpleBus\Event\Bus\RemembersNext;

class SpecializedEventBus implements EventBus
{
    use RemembersNext;

    public function handle(Event $event)
    {
        ...

        // call the next event bus in the chain
        $this->next($event);
    }
}
```

### Load event handlers in a different way

The `DelegatesToEventHandlers` event bus uses a `EventHandlersResolver` to find the right handlers for a given
event object. You can implement your own strategy for that of course, just make sure your class implements the
`EventHandlersResolver` interface.
