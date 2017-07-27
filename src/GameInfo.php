<?php

namespace BattleshipsApi\CliClient;

use BattleshipsApi\Client\Request\Event\EventTypes;

class GameInfo
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $playerNumber;

    /**
     * @var int
     */
    protected $otherNumber;

    /**
     * @var string
     */
    protected $playerName = '';

    /**
     * @var string
     */
    protected $otherName = '';

    /**
     * @var array
     */
    protected $playerShips = [];

    /**
     * @var array
     */
    protected $otherShips = [];

    /**
     * @var array
     */
    protected $playerShots = [];

    /**
     * @var array
     */
    protected $otherShots = [];

    /**
     * @var bool
     */
    protected $playerJoined = false;

    /**
     * @var bool
     */
    protected $otherJoined = false;

    /**
     * @var bool
     */
    protected $playerStarted = false;

    /**
     * @var bool
     */
    protected $otherStarted = false;

    /**
     * @var int
     */
    protected $lastEventId = 0;

    /**
     * @var int
     */
    protected $whoseTurn = 1;

    /**
     * @var array
     */
    protected $chats = [];

    /**
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getPlayerNumber(): int
    {
        return $this->playerNumber;
    }

    /**
     * @param int $playerNumber
     * @return $this|GameInfo
     */
    public function setPlayerNumber(int $playerNumber): self
    {
        $this->playerNumber = $playerNumber;

        return $this;
    }

    /**
     * @return int
     */
    public function getOtherNumber(): int
    {
        return $this->otherNumber;
    }

    /**
     * @param int $otherNumber
     * @return $this|GameInfo
     */
    public function setOtherNumber(int $otherNumber): self
    {
        $this->otherNumber = $otherNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlayerName(): string
    {
        return $this->playerName;
    }

    /**
     * @param string $playerName
     * @return $this|GameInfo
     */
    public function setPlayerName(string $playerName): self
    {
        $this->playerName = $playerName;

        return $this;
    }

    /**
     * @return string
     */
    public function getOtherName(): string
    {
        return $this->otherName;
    }

    /**
     * @param string $otherName
     * @return $this|GameInfo
     */
    public function setOtherName(string $otherName): self
    {
        $this->otherName = $otherName;

        return $this;
    }

    /**
     * @return array
     */
    public function getPlayerShips(): array
    {
        return $this->playerShips;
    }

    /**
     * @param array $playerShips
     * @return $this|GameInfo
     */
    public function setPlayerShips(array $playerShips): self
    {
        $this->playerShips = $playerShips;

        return $this;
    }

    /**
     * @return array
     */
    public function getOtherShips(): array
    {
        return $this->otherShips;
    }

    /**
     * @param array $otherShips
     * @return $this|GameInfo
     */
    public function setOtherShips(array $otherShips): self
    {
        $this->otherShips = $otherShips;

        return $this;
    }

    /**
     * @return array
     */
    public function getPlayerShots(): array
    {
        return $this->playerShots;
    }

    /**
     * @param string $coords
     * @param string $result
     * @return $this|GameInfo
     */
    public function addPlayerShot(string $coords, string $result): self
    {
        $this->playerShots[$coords] = $result;

        return $this;
    }

    /**
     * @return array
     */
    public function getOtherShots(): array
    {
        return $this->otherShots;
    }

    /**
     * @param string $coords
     * @param string $result
     * @return $this|GameInfo
     */
    public function addOtherShot(string $coords, string $result): self
    {
        $this->otherShots[$coords] = $result;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPlayerJoined(): bool
    {
        return $this->playerJoined;
    }

    /**
     * @param bool $playerJoined
     * @return $this|GameInfo
     */
    public function setPlayerJoined(bool $playerJoined): self
    {
        $this->playerJoined = $playerJoined;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOtherJoined(): bool
    {
        return $this->otherJoined;
    }

    /**
     * @param bool $otherJoined
     * @return $this|GameInfo
     */
    public function setOtherJoined(bool $otherJoined): self
    {
        $this->otherJoined = $otherJoined;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPlayerStarted(): bool
    {
        return $this->playerStarted;
    }

    /**
     * @param bool $playerStarted
     * @return $this|GameInfo
     */
    public function setPlayerStarted(bool $playerStarted): self
    {
        $this->playerStarted = $playerStarted;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOtherStarted(): bool
    {
        return $this->otherStarted;
    }

    /**
     * @param bool $otherStarted
     * @return $this|GameInfo
     */
    public function setOtherStarted(bool $otherStarted): self
    {
        $this->otherStarted = $otherStarted;

        return $this;
    }

    /**
     * @return int
     */
    public function getLastEventId(): int
    {
        return $this->lastEventId;
    }

    /**
     * @param int $lastEventId
     * @return $this|GameInfo
     */
    public function setLastEventId(int $lastEventId): self
    {
        $this->lastEventId = $lastEventId;

        return $this;
    }

    /**
     * @return int
     */
    public function getWhoseTurn(): int
    {
        return $this->whoseTurn;
    }

    /**
     * @param int $whoseTurn
     * @return $this|GameInfo
     */
    public function setWhoseTurn(int $whoseTurn): self
    {
        $this->whoseTurn = $whoseTurn;

        return $this;
    }

    /**
     * @return array
     */
    public function getChats(): array
    {
        return $this->chats;
    }

    /**
     * @param string $chat
     * @return $this|GameInfo
     */
    public function addChat(string $chat): self
    {
        $this->chats[] = $chat;

        return $this;
    }

    /**
     * @param mixed $json
     * @return $this|GameInfo
     * @throws \InvalidArgumentException
     */
    public function setFromGetGameJson($json): self
    {
        if ($json->id !== $this->id) {
            throw new \InvalidArgumentException('Game id from response must match class game id');
        }

        return $this
            ->setPlayerName($json->player->name ?? '')
            ->setOtherName($json->other->name ?? '')
            ->setPlayerShips($json->playerShips)
            ->setPlayerNumber($json->playerNumber)
            ->setOtherNumber($json->playerNumber === 1 ? 2 : 1)
        ;
    }

    /**
     * @param mixed $json
     * @return $this|GameInfo
     * @throws \InvalidArgumentException
     */
    public function setFromGetEventsJson($json): self
    {
        foreach ($json as $event) {
            switch ($event->type) {
                case EventTypes::EVENT_TYPE_JOIN_GAME:
                    if ($event->player === $this->playerNumber) {
                        $this->setPlayerJoined(true);
                    } else {
                        $this->setOtherJoined(true);
                    }
                    break;
                case EventTypes::EVENT_TYPE_START_GAME:
                    if ($event->player === $this->playerNumber) {
                        $this->setPlayerStarted(true);
                    } else {
                        $this->setOtherStarted(true);
                    }
                    break;
                case EventTypes::EVENT_TYPE_SHOT:
                    // e.g. A1|hit, G6|hit, J6|sunk
                    $shotInfo = explode('|', $event->value);
                    if ($event->player === $this->playerNumber) {
                        $this->addPlayerShot($shotInfo[0], $shotInfo[1]);
                        if ($shotInfo[1] === 'miss') {
                            $this->setWhoseTurn($this->otherNumber);
                        }
                    } else {
                        $this->addOtherShot($shotInfo[0], $shotInfo[1]);
                        if ($shotInfo[1] === 'miss') {
                            $this->setWhoseTurn($this->playerNumber);
                        }
                    }
                    break;
                case EventTypes::EVENT_TYPE_NAME_UPDATE:
                    if ($event->player === $this->playerNumber) {
                        $this->setPlayerName($event->value);
                    } else {
                        $this->setOtherName($event->value);
                    }
                    break;
                case EventTypes::EVENT_TYPE_NEW_GAME:
                default:
                    break;
            }

            $this->setLastEventId($event->id);
        }

        return $this;
    }

    /**
     * @param string $coords
     * @param int $board
     * @return string
     */
    public function getCoordsStatus(string $coords, int $board): string
    {
        // for board 1 results from other shot or ship if set, for board 2 result from player shot
        return $board === 1
            ? ($this->otherShots[$coords] ?? (in_array($coords, $this->playerShips) ? 'ship' : ''))
            : ($this->playerShots[$coords] ?? '')
        ;
    }
}
