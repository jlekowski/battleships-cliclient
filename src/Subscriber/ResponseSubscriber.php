<?php

namespace BattleshipsApi\CliClient\Subscriber;

use BattleshipsApi\CliClient\Board\CursorHandler;
use BattleshipsApi\CliClient\Board\Writer;
use BattleshipsApi\CliClient\GameInfo;
use BattleshipsApi\Client\Event\ApiClientEvents;
use BattleshipsApi\Client\Event\PostRequestEvent;
use BattleshipsApi\Client\Request\Event\EventTypes;
use BattleshipsApi\Client\Request\Event\GetEventsRequest;
use BattleshipsApi\Client\Request\Game\GetGameRequest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ResponseSubscriber implements EventSubscriberInterface
{
    /**
     * @var Writer
     */
    protected $writer;

    /**
     * @var CursorHandler
     */
    protected $cursorHandler;

    /**
     * @var GameInfo
     */
    protected $gameInfo;

    /**
     * @param Writer $writer
     * @param CursorHandler $cursorHandler
     * @param GameInfo $gameInfo
     */
    public function __construct(Writer $writer, CursorHandler $cursorHandler, GameInfo $gameInfo)
    {
        $this->writer = $writer;
        $this->cursorHandler = $cursorHandler;
        $this->gameInfo = $gameInfo;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ApiClientEvents::POST_REQUEST => [
                ['onGetGameResponse', -50],
                ['onGetEventsResponse', -50]
            ]
        ];
    }

    /**
     * @param PostRequestEvent $event
     */
    public function onGetGameResponse(PostRequestEvent $event)
    {
        if (!($event->getRequest() instanceof GetGameRequest)) {
            return;
        }

        $json = $event->getResponse()->getJson();
        $this->gameInfo->setFromGetGameJson($json);

        foreach ($json->playerShips as $ship) {
            $this->writer->writeBoard($ship, 1, Writer::MARKS['ship']);
        }

        $this->writer->writeName(1, $json->player->name ?? '');
        $this->writer->writeName(2, $json->other->name ?? '');
    }

    /**
     * @param PostRequestEvent $event
     */
    public function onGetEventsResponse(PostRequestEvent $event)
    {
        if (!($event->getRequest() instanceof GetEventsRequest)) {
            return;
        }

        $json = $event->getResponse()->getJson();
        $this->gameInfo->setFromGetEventsJson($json);

        $isPlayerTurn = $this->gameInfo->getWhoseTurn() === $this->gameInfo->getPlayerNumber();
        $this->writer->writeName(1, $this->gameInfo->getPlayerName(), $isPlayerTurn);
        $this->writer->writeName(2, $this->gameInfo->getOtherName(), !$isPlayerTurn);
        if ($this->gameInfo->getPlayerNumber() === 1) {
            $this->gameInfo->setPlayerJoined(true);
        }

        foreach ($json as $event) {
            switch ($event->type) {
                case EventTypes::EVENT_TYPE_SHOT:
                    $shotInfo = explode('|', $event->value);
                    $board = $event->player === $this->gameInfo->getPlayerNumber() ? 2 : 1;
                    $this->writer->writeBoard($shotInfo[0], $board, Writer::MARKS[$shotInfo[1]]);
                    break;

                case EventTypes::EVENT_TYPE_START_GAME:
                    if ($event->player === $this->gameInfo->getPlayerNumber()) {
                        $this->cursorHandler->toBoard(2);
                    }
                    break;

                default:
                    break;
            }
        }
    }
}
