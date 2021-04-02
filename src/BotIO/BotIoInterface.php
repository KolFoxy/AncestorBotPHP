<?php

namespace Ancestor\BotIO;

use React\EventLoop\TimerInterface;

interface BotIoInterface {
    /**
     * @return mixed
     */
    public function getLoop();

    public function getUser(): UserInterface;

    /**
     * @param int $timeout
     * @param callable $callback
     */
    public function addTimer(int $timeout, $callback): TimerInterface;

    public function cancelTimer(TimerInterface $timer): void;

}