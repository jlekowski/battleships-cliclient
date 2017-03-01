<?php

namespace BattleshipsApi\CliClient\Board;

use CLI\Cursor;

class CursorHandler
{
    /**
     * @var CursorInfo
     */
    protected $cursorInfo;

    /**
     * Left border
     * @var int
     */
    protected $xStart = 3;

    /**
     * Bottom border
     * @var int
     */
    protected $yStart = 3;

    /**
     * Right border
     * @var int
     */
    protected $xEnd = 39;

    /**
     * Top border
     * @var int
     */
    protected $yEnd = 21;

    /**
     * Number of columns for one move
     * @var int
     */
    protected $xStep = 4;

    /**
     * Number of lines for one move
     * @var int
     */
    protected $yStep = 2;

    /**
     * Number of columns between corresponding fields of different boards (e.g. H5 on board 1 and H5 on board 2)
     * @var int
     */
    protected $boardOffset = 47;

    /**
     * @param CursorInfo $cursorInfo
     */
    public function __construct(CursorInfo $cursorInfo)
    {
        $this->cursorInfo = $cursorInfo;
    }

    public function moveX(int $offset):self
    {
        // column number if moved
        $newColumn = $this->cursorInfo->getCurrentColumn() + $offset;
        // which board cursor is on
        $currentBoard = $this->cursorInfo->getCurrentColumn() > $this->boardOffset ? 2 : 1;
        // left border taking into consideration board cursor is on and board switch
        $xStart = $this->xStart + ($currentBoard === 2  && $offset !== -$this->boardOffset ? $this->boardOffset : 0);
        // right border taking into consideration board cursor is on and board switch
        $xEnd = $this->xEnd + ($currentBoard === 2 || $offset === $this->boardOffset ? $this->boardOffset : 0);
        // would be within borders
        $isAllowedToMove = ($newColumn >= $xStart) && ($newColumn <= $xEnd);

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

    public function moveY(int $offset):self
    {
        // column number if moved
        $newLine = $this->cursorInfo->getCurrentLine() + $offset;
        // would be within borders
        $isAllowedToMove = ($newLine >= $this->yStart) && ($newLine <= $this->yEnd);

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

    public function moveUp():self
    {
        return $this->moveY($this->yStep);
    }

    public function moveDown():self
    {
        return $this->moveY(-$this->yStep);
    }

    public function moveRight():self
    {
        return $this->moveX($this->xStep);
    }

    public function moveLeft():self
    {
        return $this->moveX(-$this->xStep);
    }

    public function toBoard(int $board): self
    {
        if ($board === 1) {
            Cursor::forward(-$this->boardOffset);
        } else {
            Cursor::back($this->boardOffset);
        }
        $this->cursorInfo->setBoard($board);

        return $this->moveX($board === 1 ? -$this->boardOffset : $this->boardOffset);
    }

    public function switchBoard(): self
    {
        return $this->toBoard($this->cursorInfo->getCurrentColumn() > $this->xEnd ? 1 : 2);
    }
}
