<?php

namespace BattleshipsApi\CliClient;

use BattleshipsApi\CliClient\Board\CursorHandler;
use BattleshipsApi\CliClient\Board\CursorInfo;
use BattleshipsApi\CliClient\Board\Writer;
use BattleshipsApi\Client\Client\ApiClient;
use BattleshipsApi\Client\Exception\ApiException;
use BattleshipsApi\Client\Request\Event\CreateEventRequest;
use BattleshipsApi\Client\Request\Event\EventTypes;
use BattleshipsApi\Client\Request\Event\GetEventsRequest;
use BattleshipsApi\Client\Request\Game\GetGameRequest;
use BattleshipsApi\Client\Request\Game\UpdateGameRequest;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;

class GameManager
{
    /** Array with Y axis elements */
    /* protected */ const AXIS_Y = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
    /** Array with X axis elements */
    /* protected */ const AXIS_X = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];

    /**
     * @var ApiClient
     */
    protected $apiClient;

    /**
     * @var CursorHandler
     */
    protected $cursorHandler;

    /**
     * @var bool
     */
    protected $keepRunning = false;

    /**
     * @var GameInfo
     */
    protected $gameInfo;

    /**
     * @var Writer
     */
    protected $writer;

    /**
     * @param ApiClient $apiClient
     * @param CursorHandler $cursorHandler
     * @param Writer $writer
     */
    public function __construct(ApiClient $apiClient, CursorHandler $cursorHandler, Writer $writer)
    {
        $this->apiClient = $apiClient;
        $this->cursorHandler = $cursorHandler;
        $this->writer = $writer;
    }

    /**
     * @param GameInfo $gameInfo
     * @throws \BattleshipsApi\Client\Exception\ApiException
     * @throws ExceptionInterface
     */
    public function run(GameInfo $gameInfo)
    {
        $this->gameInfo = $gameInfo;
        $gameId = $gameInfo->getId();

        $this->writer->writeBattleground($this->gameInfo);
        $this->cursorHandler->enterBattleground();

        $request = new GetGameRequest();
        $request->setGameId($gameId);
        $this->apiClient->call($request);

        $request = new GetEventsRequest();
        $request->setGameId($gameId);
        $this->apiClient->call($request);

        $this->keepRunning = true;
    }

    /**
     * Stop the game
     */
    public function stop()
    {
        $this->cursorHandler->exitBattleground();
        $this->keepRunning = false;
    }

    /**
     * @return bool
     */
    public function keepRunning(): bool
    {
        return $this->keepRunning;
    }

    /**
     * @param string $coords
     * @throws ApiException
     * @throws ExceptionInterface
     */
    public function shoot(string $coords)
    {
        if ($this->gameInfo->getWhoseTurn() === $this->gameInfo->getPlayerNumber()) {
            $request = new CreateEventRequest();
            $request
                ->setGameId($this->gameInfo->getId())
                ->setEventType(EventTypes::EVENT_TYPE_SHOT)
                ->setEventValue($coords)
            ;
            $apiResponse = $this->apiClient->call($request);
            $result = $apiResponse->getJson()->result;
            $this->gameInfo->addPlayerShot($coords, $result);
            $this->writer->writeBoard($coords, 2, Writer::MARKS[$result]);
        } else {
            $this->writer->writeConsole('not your turn');
        }
    }

    /**
     * @throws ApiException
     * @throws ExceptionInterface
     * @throws \RuntimeException
     */
    public function start()
    {
        if ($this->gameInfo->isPlayerStarted()) {
            throw new \RuntimeException('Cannot start the game - game has already started');
        }
        $ships = $this->gameInfo->getPlayerShips();

        $request = new UpdateGameRequest();
        $request
            ->setGameId($this->gameInfo->getId())
            ->setPlayerShips($ships)
        ;
        if (!$this->gameInfo->isPlayerJoined()) {
            $request->setJoinGame(true);
        }
        $this->apiClient->call($request);
        $this->gameInfo->setPlayerJoined(true);
        $this->gameInfo->setPlayerStarted(true);
        $this->cursorHandler->toBoard(CursorInfo::BOARD_OTHER);
    }

    /**
     * @param string $coords
     */
    public function toggleShip(string $coords)
    {
        if ($this->gameInfo->isPlayerStarted()) {
            return;
        }

        if (in_array($coords, $this->gameInfo->getPlayerShips())) {
            $this->gameInfo->setPlayerShips(array_diff($this->gameInfo->getPlayerShips(), [$coords]));
            $this->writer->writeBoard($coords, 1, '  ');
        } else {
            $this->gameInfo->setPlayerShips(array_merge($this->gameInfo->getPlayerShips(), [$coords]));
            $this->writer->writeBoard($coords, 1, Writer::MARKS['ship']);
        }
    }

    /**
     * @throws ApiException
     * @throws ExceptionInterface
     */
    public function getUpdates()
    {
        $request = new GetEventsRequest();
        $request
            ->setGameId($this->gameInfo->getId())
            ->setGt($this->gameInfo->getLastEventId())
            ->setPlayer($this->gameInfo->getOtherNumber())
        ;
        $this->apiClient->call($request);
    }
}
