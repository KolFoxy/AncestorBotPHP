<?php

namespace Ancestor\BotIO;

interface BotIoInterface {
    /**
     * @return mixed
     */
    public function getLoop();

    public function getUser() : UserInterface;
}