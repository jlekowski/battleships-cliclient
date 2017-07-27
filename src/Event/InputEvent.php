<?php

namespace BattleshipsApi\CliClient\Event;

use Symfony\Component\EventDispatcher\Event;

class InputEvent extends Event
{
    /**
     * @var string
     */
    protected $input;

    /**
     * @param string $input
     */
    public function __construct(string $input)
    {
        $this->input = $input;
    }

    /**
     * @return string
     */
    public function getInput(): string
    {
        return $this->input;
    }
}
