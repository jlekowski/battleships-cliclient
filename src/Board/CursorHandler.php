<?php

namespace BattleshipsApi\CliClient\Board;

use BattleshipsApi\CliClient\GameManager;
use CLI\Cursor;

class CursorHandler
{
    /**
     * Left border
     */
    const START_X = 6;

    /**
     * Bottom border
     */
    const START_Y = 4;

    /**
     * Right border
     */
    const END_X = 42;

    /**
     * Top border
     */
    const END_Y = 22;

    /**
     * Number of columns for one move
     */
    const STEP_X = 4;

    /**
     * Number of lines for one move
     */
    const STEP_Y = 2;

    /**
     * Number of columns between corresponding fields of different boards (e.g. H5 on board 1 and H5 on board 2)
     */
    const OFFSET_BOARD = 47;

    /**
     * @var CursorInfo
     */
    protected $cursorInfo;

    /**
     * Saved cursor's position
     * @var array
     */
    protected $savedPosition = ['line' => self::START_Y, 'column' => self::START_X, 'board' => 1];

    /**
     * @param CursorInfo $cursorInfo
     */
    public function __construct(CursorInfo $cursorInfo)
    {
        $this->cursorInfo = $cursorInfo;
    }

    /**
     * @param int $offset
     * @return $this|CursorHandler
     */
    public function moveX(int $offset): self
    {
        // column number if moved
        $newColumn = $this->cursorInfo->getCurrentColumn() + $offset;
        // would be within borders
        $isAllowedToMove = ($newColumn >= self::START_X) && ($newColumn <= self::END_X);

        if ($isAllowedToMove) {
            if ($offset > 0) {
                Cursor::forward($offset);
            } elseif ($offset < 0) {
                Cursor::back(-$offset);
            }
            $this->cursorInfo->movedColumns($offset);
        }

        return $this;
    }

    /**
     * @param int $offset
     * @return $this|CursorHandler
     */
    public function moveY(int $offset): self
    {
        // column number if moved
        $newLine = $this->cursorInfo->getCurrentLine() + $offset;
        // would be within borders
        $isAllowedToMove = ($newLine >= self::START_Y) && ($newLine <= self::END_Y);

        if ($isAllowedToMove) {
            if ($offset > 0) {
                Cursor::up($offset);
            } elseif ($offset < 0) {
                Cursor::down(-$offset);
            }
            $this->cursorInfo->movedLines($offset);
        }

        return $this;
    }

    /**
     * @return $this|CursorHandler
     */
    public function moveUp(): self
    {
        return $this->moveY(self::STEP_Y);
    }

    /**
     * @return $this|CursorHandler
     */
    public function moveDown(): self
    {
        return $this->moveY(-self::STEP_Y);
    }

    /**
     * @return $this|CursorHandler
     */
    public function moveRight(): self
    {
        return $this->moveX(self::STEP_X);
    }

    /**
     * @return $this|CursorHandler
     */
    public function moveLeft(): self
    {
        return $this->moveX(-self::STEP_X);
    }

    /**
     * @param string $coords
     * @param int $board
     * @return $this|CursorHandler
     */
    public function moveToCoords(string $coords, int $board): self
    {
        $indexX = array_search(substr($coords, 1), GameManager::AXIS_X);
        $indexY = array_search($coords[0], GameManager::AXIS_Y);

        $offsetX = $indexX - ($this->cursorInfo->getCurrentColumn() - self::START_X) / self::STEP_X;
        $offsetY = 9 - $indexY - ($this->cursorInfo->getCurrentLine() - self::START_Y) / self::STEP_Y;

        return $this->toBoard($board)->moveX($offsetX * self::STEP_X)->moveY($offsetY * self::STEP_Y);
    }

    /**
     * @param int $board
     * @return $this|CursorHandler
     */
    public function toBoard(int $board): self
    {
        if ($this->cursorInfo->getBoard() !== $board) {
            if ($board === 1) {
                Cursor::back(self::OFFSET_BOARD);
            } else {
                Cursor::forward(self::OFFSET_BOARD);
            }
            $this->cursorInfo->setBoard($board);
        }

        return $this;
    }

    /**
     * @return $this|CursorHandler
     */
    public function switchBoard(): self
    {
        return $this->toBoard($this->cursorInfo->getBoard() === 1 ? 2 : 1);
    }

    /**
     * @return $this|CursorHandler
     */
    public function enterBattleground(): self
    {
        fwrite(Cursor::$stream, "\r"); // move to the beginning of the line
        Cursor::forward(self::START_X);
        Cursor::up(self::START_Y);
        Cursor::show();
        $this->cursorInfo->setBoard(1)->movedColumns(self::START_X)->movedLines(self::START_Y);

        return $this;
    }

    /**
     * @return $this|CursorHandler
     */
    public function exitBattleground(): self
    {
        $moveX = $this->cursorInfo->getCurrentColumn() + ($this->cursorInfo->getBoard() === 2 ? self::OFFSET_BOARD : 0);
        $moveY = $this->cursorInfo->getCurrentLine() + 2;
        fwrite(Cursor::$stream, "\r"); // move to the beginning of the line
        Cursor::down($moveY);
        Cursor::show();
        $this->cursorInfo->setBoard(1)->movedColumns($moveX)->movedLines($moveY);

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentCoords(): string
    {
        $indexX = ($this->cursorInfo->getCurrentColumn() - self::START_X) / self::STEP_X; // (current column - initial offset) / column step
        $indexY = 9 - ($this->cursorInfo->getCurrentLine() - self::START_Y) / self::STEP_Y; // array size - (current line - initial offset) / line step

        return GameManager::AXIS_Y[$indexY] . GameManager::AXIS_X[$indexX];
    }

    /**
     * @return $this|CursorHandler
     */
    public function save(): self
    {
        $this->savedPosition = [
            'column' => $this->cursorInfo->getCurrentColumn(),
            'line' => $this->cursorInfo->getCurrentLine(),
            'board' => $this->cursorInfo->getBoard()
        ];

        return $this;
    }

    /**
     * @return $this|CursorHandler
     */
    public function restore(): self
    {
        return $this
            ->moveX($this->savedPosition['column'] - $this->cursorInfo->getCurrentColumn())
            ->moveY($this->savedPosition['line'] - $this->cursorInfo->getCurrentLine())
            ->toBoard($this->savedPosition['board'])
        ;
    }
}
