<?php

namespace BattleshipsApi\CliClient\Subscriber;

use BattleshipsApi\CliClient\Board\CursorHandler;
use BattleshipsApi\CliClient\Board\CursorInfo;
use BattleshipsApi\CliClient\Board\Writer;
use BattleshipsApi\CliClient\Event\CliClientEvents;
use BattleshipsApi\CliClient\Event\InputEvent;
use BattleshipsApi\CliClient\GameInfo;
use BattleshipsApi\CliClient\GameManager;
use BattleshipsApi\Client\Client\ApiClient;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TerminalInputSubscriber implements EventSubscriberInterface
{
    /**
     * @var GameManager
     */
    protected $gameManager;

    /**
     * @var GameInfo
     */
    protected $gameInfo;

    /**
     * @var ApiClient
     */
    protected $apiClient;

    /**
     * @var CursorInfo
     */
    protected $cursorInfo;

    /**
     * @var CursorHandler
     */
    protected $cursorHandler;

    /**
     * @var Writer
     */
    protected $writer;

    /**
     * @param GameManager $gameManager
     * @param CursorInfo $cursorInfo
     * @param CursorHandler $cursorHandler
     * @param Writer $writer
     */
    public function __construct(GameManager $gameManager, CursorInfo $cursorInfo, CursorHandler $cursorHandler, Writer $writer)
    {
        $this->gameManager = $gameManager;
        $this->cursorInfo = $cursorInfo;
        $this->cursorHandler = $cursorHandler;
        $this->writer = $writer;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            CliClientEvents::ON_INPUT => [
                ['onEsc', 10],
                ['onEnter', 0],
                ['onSpace', 0],
                ['onUp', 0],
                ['onDown', 0],
                ['onRight', 0],
                ['onLeft', 0],
                ['onTab', 0],
                ['onBackspace', 0],
                ['onDelete', 0],
                ['onOther', -10],
            ]
        ];
    }

    /**
     * @param InputEvent $event
     */
    public function onEsc(InputEvent $event)
    {
        // esc code
        if ($event->getInput() !== chr(27)) {
            return;
        }

        $this->gameManager->stop();
        $event->stopPropagation();
    }

    /**
     * @param InputEvent $event
     * @throws \BattleshipsApi\Client\Exception\ApiException
     * @throws \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     */
    public function onEnter(InputEvent $event)
    {
        if ($event->getInput() !== chr(10)) {
            return;
        }

        if ($this->cursorInfo->getBoard() === CursorInfo::BOARD_OTHER) {
            // should enter shoot or space?
        } else {
             $this->gameManager->start();
        }

        $event->stopPropagation();
    }

    /**
     * @param InputEvent $event
     * @throws \BattleshipsApi\Client\Exception\ApiException
     * @throws \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     */
    public function onSpace(InputEvent $event)
    {
        if ($event->getInput() !== ' ') {
            return;
        }

        $coords = $this->cursorHandler->getCurrentCoords();
        if ($this->cursorInfo->getBoard() === CursorInfo::BOARD_OTHER) {
            $this->gameManager->shoot($coords);
        } else {
            $this->gameManager->toggleShip($coords);
        }

        $event->stopPropagation();
    }

    /**
     * @param InputEvent $event
     */
    public function onUp(InputEvent $event)
    {
        // chr(27) \033"
        if ($event->getInput() !== "\x1B[A") {
            return;
        }

        $this->cursorHandler->moveUp();
        $event->stopPropagation();
    }

    /**
     * @param InputEvent $event
     */
    public function onDown(InputEvent $event)
    {
        if ($event->getInput() !== "\x1B[B") {
            return;
        }

        $this->cursorHandler->moveDown();
        $event->stopPropagation();
    }

    /**
     * @param InputEvent $event
     */
    public function onRight(InputEvent $event)
    {
        if ($event->getInput() !== "\x1B[C") {
            return;
        }

        $this->cursorHandler->moveRight();
        $event->stopPropagation();
    }

    /**
     * @param InputEvent $event
     */
    public function onLeft(InputEvent $event)
    {
        if ($event->getInput() !== "\x1B[D") {
            return;
        }

        $this->cursorHandler->moveLeft();
        $event->stopPropagation();
    }

    /**
     * @param InputEvent $event
     */
    public function onTab(InputEvent $event)
    {
        if ($event->getInput() !== chr(9)) {
            return;
        }

        $event->stopPropagation();
    }

    /**
     * @param InputEvent $event
     */
    public function onBackspace(InputEvent $event)
    {
        if ($event->getInput() !== chr(127)) {
            return;
        }

        $this->writer->eraseConsole();
        $event->stopPropagation();
    }

    /**
     * @param InputEvent $event
     */
    public function onDelete(InputEvent $event)
    {
        if ($event->getInput() !== "\x1B\x5B\x33\x7E") {
            return;
        }

        $this->writer->writeConsole('delete');
        $event->stopPropagation();
    }

    /**
     * @param InputEvent $event
     */
    public function onOther(InputEvent $event)
    {
        $this->writer->writeConsole($event->getInput());
    }
}
