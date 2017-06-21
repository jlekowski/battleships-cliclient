<?php

namespace BattleshipsApi\CliClient\Command;

use BattleshipsApi\Client\Client\ApiClientFactory;
use BattleshipsApi\Client\Request\User\GetUserRequest;
use CLI\Cursor;
use CLI\Erase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GameCommand extends Command
{
    /** Array with Y axis elements */
    /* protected */ const AXIS_Y = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
    /** Array with X axis elements */
    /* protected */ const AXIS_X = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];

    protected $currentLine = 0;
    protected $currentColumn = 0;
    protected $board = 1;
    protected $consoleCursor = 1;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('game')
            ->setDescription('Temp command')
            ->addArgument('url', InputArgument::OPTIONAL, 'API url', 'http://battleships-api.dev.lekowski.pl:6081/v1')
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MSwidG9rZW4iOiJhYjQ4ZGViODQ1OGE5NTI2ZmJkMTc4ZjJhMTA2MjkyOCJ9.yZGIMiWFHLZi0KtP5VDrf9PnlMZBK82tJksGVXFRSlI';
        $apiClient = ApiClientFactory::build([
            'baseUri' => 'http://battleships-api.vagrant:8080',
            'version' => 1,
            'key' => $token
        ]);
        $request = new GetUserRequest();
        $request->setUserId(1);

        stream_set_blocking(STDIN, false);
        system('stty -icanon -echo');

        Erase::screen();

        $battleground = $this->getBattleground();
        $this->write($battleground);

//        $currentLine = count(explode(PHP_EOL, $battleground));
        $this->board = 1;
        $this->currentColumn += 6;
        Cursor::forward(6);
//        Cursor::restore();
        $this->currentLine += 2;
        Cursor::up(2);
//        Cursor::save();
        Cursor::show();
        while (true) {
//            $input = fgetc(STDIN);
            $input = fgets(STDIN);

            if ($input) {
                if ($input === chr(10)) {
//                    Cursor::unsave();
                } else {

////                $apiResponse = $apiClient->call($request);
//                    $this->write("\x1B[2K");
////                Cursor::back(strlen($input) + 1);
//                    Cursor::back(strlen($input) > 1 ? strlen($input) + 1 : 1);
//                    Cursor::savepos();
////                Cursor::back(0);
//                    $this->write("\x0D");
//                    $this->write(explode(PHP_EOL, $battleground)[$currentLine - 1]);
//                    Cursor::restore();
////                Cursor::savepos();
                }

                switch ($input) {
                    // chr(27) \033"
                    case "\x1B[A": // up
//                        $this->write('up');
                        if ($this->currentLine < 20) {
                            $this->currentLine += 2;
                            Cursor::up(2);
                        }
                        break;
                    case "\x1B[B": // down
//                        $this->write('down');
                        if ($this->currentLine > 2) {
                            $this->currentLine -= 2;
                            Cursor::down(2);
                        }
                        break;
                    case "\x1B[C": // right
//                        $this->write('right');
                        if ($this->currentColumn < 42) {
                            $this->currentColumn += 4;
                            Cursor::forward(4);
                        }
                        break;
                    case "\x1B[D": // left
//                        $this->write('left');
                        if ($this->currentColumn > 6) {
                            $this->currentColumn -= 4;
                            Cursor::back(4);
                        }
                        break;
                    case chr(10): // enter
//                        Cursor::up();
//                        Cursor::forward($currentColumnt);
                        Cursor::hide();
                        $apiResponse = $apiClient->call($request);
                        Cursor::show();
                        break;
                    case chr(9): // tab
                        if ($this->board === 1) {
                            $this->board = 2;
                            Cursor::forward(47);
                        } else {
                            $this->board = 1;
                            Cursor::back(47);
                        }
                        break;
                    case chr(127): // backspace
                        $this->consoleEraseChar();
                        break;
                    case chr(27): // esc
                        Cursor::back($this->currentColumn + ($this->board === 2 ? 47 : 0));
                        Cursor::down($this->currentLine);
                        Cursor::show();
                        system('stty icanon echo');
                        return;
                    case '':
                    default:
                        if (strlen($input) == 1) {
                            $this->consoleWrite($input);
//                            $this->consoleWrite(ord($input));
                        }
//                        Cursor::down();
//                        Graphics::box(3, 3, 13, 13);
//                        $i++;
//                        Graphics::line($i, $i, $i + 10, $i);
                        break;
                }
//
//                $this->write("\x1B[2K");

//                file_put_contents('/home/jerzy/dev/private/battleships-apiclient/temp.log', $input, FILE_APPEND);
//            $this->write("\x0D");
//                $this->write("\x1B[2K");
//            $this->write(str_repeat("\x1B[1A\x1B[2K", 1));
//            $this->write("\033[K");
//            $this->write("\r");
//                $this->write(Cursor::back(strlen($input) + 1));
//                $this->write($input);
//                $this->write(ord($input));
            }

            usleep(500);
        }
    }

    private function getBattleground()
    {
        $marks = array('ship' => "S", 'miss' => ".", 'hit' => "x", 'sunk' => "X");
        $board  = sprintf(
            "\n     % 39.39s        % 39.39s \n\n",
            "Player 1",
            "Player 2"
        );
        $board .= "    ";

        // 11 rows (first row for X axis labels)
        for ($i = 0; $i < 11; $i++) {
            // 11 divs/columns in each row (first column for Y axis labels)
            for ($j = 0; $j < 11; $j++) {
                if ($i == 0 && $j > 0) {
                    $text = self::AXIS_X[($j - 1)];
                    $board .= sprintf(" % 2s ", $text);
                } elseif ($j == 0 && $i > 0) {
                    $text = self::AXIS_Y[($i - 1)];
                    $board .= sprintf(" % 2s |", $text);
                } elseif ($j > 0 && $i > 0) {
                    $coords = self::AXIS_Y[($i - 1)] . self::AXIS_X[($j - 1)];
//                    $text = isset($battle->playerGround->{$coords})
//                        ? $marks[ $battle->playerGround->{$coords} ]
//                        : "";
                    $text = '';
                    $board .= sprintf(" % 1s |", $text);
                }
            }

            $board .= "  ";
            for ($j = 0; $j < 11; $j++) {
                if ($i == 0 && $j > 0) {
                    if ($j == 1) {
                        $board .= "     ";
                    }
                    $text = self::AXIS_X[($j - 1)];
                    $board .= sprintf(" % 2s ", $text);
                } elseif ($j == 0 && $i > 0) {
                    $text = self::AXIS_Y[($i - 1)];
                    $board .= sprintf(" % 2s |", $text);
                } elseif ($j > 0 && $i > 0) {
                    $coords = self::AXIS_Y[($i - 1)] . self::AXIS_X[($j - 1)];
//                    $text = isset($battle->otherGround->{$coords})
//                        ? $marks[ $battle->otherGround->{$coords} ]
//                        : "";
                    $text = '';
                    $board .= sprintf(" % 1s |", $text);
                }
            }
            $board .= "\n    ".str_repeat("+---", 10)."+";
            $board .= "      ".str_repeat("+---", 10)."+\n";
        }

        return $board;
    }

    protected function write($t) {
        fwrite(STDERR, $t);
    }

    protected function consoleWrite($t)
    {
        Cursor::save();
        Cursor::back($this->currentColumn + ($this->board === 2 ? 47 : 0));
        Cursor::forward($this->consoleCursor);
        Cursor::down($this->currentLine);
        $this->write($t);
        $this->consoleCursor += strlen($t);
        Cursor::restore();
    }

    protected function consoleEraseChar()
    {
        // all erased already
        if ($this->consoleCursor <= 1) {
            return;
        }

        Cursor::save();
        Cursor::back($this->currentColumn + ($this->board === 2 ? 47 : 0));
        Cursor::forward($this->consoleCursor - 1);
        Cursor::down($this->currentLine);
        Erase::eol();
        $this->consoleCursor--;
        Cursor::restore();
    }
}
