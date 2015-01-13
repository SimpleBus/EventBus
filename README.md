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
    }
    ```

2. Create an event subscriber

    ```php
    use SimpleBus\Message\Subscriber\MessageSubscriber;
    use SimpleBus\Message\Message;

    class SendConfirmationMailWhenUserRegistered implements MessageSubscriber
    {
        public function notify(Message $event)
        {
            ...
        }
    }
    ```

3. Set up the event bus and the event subscribers resolver:

    ```php
    use SimpleBus\Message\Subscriber\Resolver\MessageSubscribersResolver;
    use SimpleBus\Message\Subscriber\Resolver\NameBasedMessageSubscriberResolver;
    use SimpleBus\Message\Name\ClassBasedNameResolver;
    use SimpleBus\Message\Subscriber\NotifiesMessageSubscribersMiddleware;
    use SimpleBus\Message\Subscriber\Collection\LazyLoadingMessageSubscriberCollection;

    $messageNameResolver = new ClassBasedNameResolver();
    $subscriberCollection = new LazyLoadingMessageSubscriberCollection(
        [
            UserRegisteredEvent::class => array(
                'send_confirmation_mail_when_user_registered_service_id',
                // add other subscriber service ids
                ...
            )
        ],
        function ($serviceId) {
            // lazily load/create instances of the given event handler service, e.g. using a service locator
             $handler = ...;

             return $handler;
        }
    );

    $eventSubscribersResolver = new NameBasedMessageSubscriberResolver(
        $messageNameResolver,
        $subscriberCollection
    );

    $eventBusMiddleware = new NotifiesMessageSubscribersMiddleware($eventSubscribersResolver);
    $eventBus->addMiddleware($eventBusMiddleware);

    $userRegisteredEvent = new UserRegisteredEvent();

    $eventBus->handle($userRegisteredEvent);
    ```

Because an event handler might call the event bus to handle new events, it's better to add a specialized middleware to
make sure that the first event is fully handled first:

```php
use SimpleBus\Message\Bus\Middleware\FinishesHandlingMessageBeforeHandlingNext;

// N.B. add this middleware before adding other middlewares
$eventBus->addMiddleware(new FinishesHandlingMessageBeforeHandlingNext());
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
