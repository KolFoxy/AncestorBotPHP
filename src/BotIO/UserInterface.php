<?php

namespace Ancestor\BotIO;

use React\Promise\PromiseInterface;

interface UserInterface {
    public function getUsername(): string;

    public function getAvatarUrl(): string;

    public function getId(): string;

    public function getMention(): string;

    /**
     * @return PromiseInterface Resolves with ChannelInterface
     */
    public function createDM(): PromiseInterface;
}