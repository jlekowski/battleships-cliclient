<?php

namespace BattleshipsApi\CliClient\Board;

class CursorInfo
{
    /**
     * Left board
     */
    const BOARD_PLAYER = 1;

    /**
     * Right board
     */
    const BOARD_OTHER = 2;

    /**
     * @var int
     */
    protected $currentLine = 0;

    /**
     * @var int
     */
    protected $currentColumn = 0;

    /**
     * @var int
     */
    protected $board = self::BOARD_PLAYER;

    /**
     * @var int
     */
    protected $consoleCursor = 4; // prompt size + 1

    /**
     * @param int $lines
     * @return $this|CursorInfo
     */
    public function movedLines(int $lines): self
    {
        $this->currentLine += $lines;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentLine(): int
    {
        return $this->currentLine;
    }

    /**
     * @param int $columns
     * @return $this|CursorInfo
     */
    public function movedColumns(int $columns): self
    {
        $this->currentColumn += $columns;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentColumn(): int
    {
        return $this->currentColumn;
    }

    /**
     * @return int
     */
    public function getBoard(): int
    {
        return $this->board;
    }

    /**
     * @param int $board
     * @return $this|CursorInfo
     */
    public function setBoard(int $board): self
    {
        $this->board = $board;

        return $this;
    }

    /**
     * @param int $columns
     * @return $this|CursorInfo
     */
    public function movedConsole(int $columns): self
    {
        $this->consoleCursor += $columns;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentConsole(): int
    {
        return $this->consoleCursor;
    }
}
