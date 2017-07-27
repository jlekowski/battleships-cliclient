<?php

namespace BattleshipsApi\CliClient\Board;

use BattleshipsApi\CliClient\GameInfo;
use BattleshipsApi\CliClient\GameManager;
use CLI\Cursor;
use CLI\Erase;

class Writer
{
    const MARKS = [
        'ship' => "\xF0\x9F\x9B\xA5", // 🛥
        'miss' => "\xE2\x98\xBC", // ☼
        'hit' => "\xE2\x98\x80", // ☀
        'sunk' => "\xE2\x9C\x9D" // ✝
    ];

    const PROMPT = '  >';

    /**
     * @var CursorInfo
     */
    protected $cursorInfo;

    /**
     * @var CursorHandler
     */
    protected $cursorHandler;

    /**
     * @var resource
     */
    protected $handler;

    /**
     * @param CursorInfo $cursorInfo
     * @param resource $handler
     * @throws \InvalidArgumentException
     */
    public function __construct(CursorInfo $cursorInfo, $handler)
    {
        if (!is_resource($handler)) {
            throw new \InvalidArgumentException('Writer handler must be a resource');
        }

        $this->cursorInfo = $cursorInfo;
        $this->handler = $handler;
        Cursor::$stream = $handler;
    }

    /**
     * @param CursorHandler $cursorHandler
     * @return $this|Writer
     */
    public function setCursorHandler(CursorHandler $cursorHandler): self
    {
        $this->cursorHandler = $cursorHandler;

        return $this;
    }

    /**
     * @param string $text
     */
    public function write(string $text)
    {
        fwrite($this->handler, $text);
    }

    /**
     * @param string $text
     */
    public function writeConsole(string $text)
    {
        Cursor::savepos();
        $this->write("\r");
        Cursor::forward($this->cursorInfo->getCurrentConsole());
        Cursor::down($this->cursorInfo->getCurrentLine() - 1);
        $this->write($text);
        $this->cursorInfo->movedConsole(mb_strlen($text));
        Cursor::restore();
    }

    public function eraseConsole()
    {
        // all erased already
        if ($this->cursorInfo->getCurrentConsole() <= CursorHandler::START_Y) {
            return;
        }

        Cursor::savepos();
        $this->write("\r");
        Cursor::forward($this->cursorInfo->getCurrentConsole() - 1);
        Cursor::down($this->cursorInfo->getCurrentLine() - 1);
        Erase::eol();
        $this->cursorInfo->movedConsole(-1);
        Cursor::restore();
    }

    /**
     * @param GameInfo $gameInfo
     */
    public function writeBattleground(GameInfo $gameInfo)
    {
        $battleground = $this->getBattleground($gameInfo);
        $this->write($battleground);
        $this->write(PHP_EOL . self::PROMPT . PHP_EOL);
    }

    /**
     * @param string $coords
     * @param int $board
     * @param string $text
     */
    public function writeBoard(string $coords, int $board, string $text)
    {
        $this->cursorHandler
            ->save()
            ->moveToCoords($coords, $board)
        ;
        $this->write('  ');
        Cursor::back(2);
        $this->write($text);
        Cursor::back(mb_strlen($text));
        $this->cursorHandler->restore();
    }

    /**
     * @param int $board
     * @param string $text
     * @param bool $isBold
     */
    public function writeName(int $board, string $text, $isBold = false)
    {
        Cursor::savepos();
        Cursor::up(CursorHandler::END_Y + CursorHandler::START_Y - $this->cursorInfo->getCurrentLine());

        // clean old name
        $this->write("\r");
        Cursor::forward(CursorHandler::OFFSET_BOARD * ($board - 1));
        $this->write(str_repeat(' ', CursorHandler::OFFSET_BOARD));

        // write new name
        $this->write("\r");
        Cursor::forward(CursorHandler::OFFSET_BOARD * $board - mb_strlen($text) - 3); // @todo const as margin
        $this->write($isBold ? "\e[1m$text\e[21m" : $text);
        Cursor::restore();
    }

    /**
     * @param GameInfo $gameInfo
     * @return string
     */
    private function getBattleground(GameInfo $gameInfo): string
    {
        $board = sprintf(
            "\n     % 39.39s        % 39.39s \n\n",
            $gameInfo->getPlayerName(),
            $gameInfo->getOtherName()
        );
        $board .= '    ';

        // 11 rows (first row for X axis labels)
        for ($i = 0; $i < 11; $i++) {
            for ($j = 0; $j < 2; $j++) {
                // 11 divs/columns in each row (first column for Y axis labels)
                for ($k = 0; $k < 11; $k++) {
                    if ($i == 0 && $k > 0) {
                        if ($j === 1 && $k === 1) {
                            $board .= '     ';
                        }
                        $text = GameManager::AXIS_X[($k - 1)];
                        $board .= sprintf(' % 2s ', $text);
                    } elseif ($k == 0 && $i > 0) {
                        $text = GameManager::AXIS_Y[($i - 1)];
                        $board .= sprintf(' % 2s |', $text);
                    } elseif ($k > 0 && $i > 0) {
                        $board .= '   |';
                    }
                }

                if ($j === 0) {
                    $board .= '  ';
                }
            }
            $board .= "\n    " . str_repeat('+---', 10) . "+";
            $board .= "      " . str_repeat('+---', 10) . "+\n";
        }

        return $board;
    }
}
