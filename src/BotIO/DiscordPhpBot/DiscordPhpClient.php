<?php

namespace Ancestor\BotIO\DiscordPhpBot;

use Ancestor\BotIO\BotIoInterface;
use Ancestor\BotIO\UserInterface;
use Discord\Discord;
use React\EventLoop\TimerInterface;

class DiscordPhpClient implements BotIoInterface {

    protected Discord $discordClient;
    protected DiscordPhpUser $user;

    public function __construct(Discord $discordClient) {
        $this->discordClient = $discordClient;
        $this->user = new DiscordPhpUser($discordClient->user, $discordClient);
    }

    public function getUser(): UserInterface {
        return $this->user;
    }

    public function addTimer(int $timeout, $callback) {
        $this->discordClient->getLoop()->addTimer($timeout, $callback);
    }

    public function cancelTimer(TimerInterface $timer): void {
        $this->discordClient->getLoop()->cancelTimer($timer);
    }

    public function getLoop() {
        return $this->discordClient->getLoop();
    }
}