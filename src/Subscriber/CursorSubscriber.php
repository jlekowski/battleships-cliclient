<?php

namespace BattleshipsApi\CliClient\Subscriber;

use BattleshipsApi\Client\Event\ApiClientEvents;
use CLI\Cursor;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CursorSubscriber implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ApiClientEvents::PRE_RESOLVE => ['onPreResolve', -100],
            ApiClientEvents::POST_REQUEST => ['onComplete', -100],
            ApiClientEvents::ON_ERROR => ['onComplete', -100]
        ];
    }

    /**
     * @param Event $event
     */
    public function onPreResolve(Event $event)
    {
        Cursor::hide();
    }

    /**
     * @param Event $event
     */
    public function onComplete(Event $event)
    {
        Cursor::show();
    }
}
