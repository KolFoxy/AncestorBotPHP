<?php

namespace Ancestor\BotIO\DiscordPhpBot;

use Ancestor\BotIO\UserInterface;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\User\User;
use React\Promise\PromiseInterface;

class DiscordPhpUser implements UserInterface {

    protected User $discordUser;
    protected Discord $discord;

    public function __construct(User $discordUser, Discord $discord) {
        $this->discordUser = $discordUser;
        $this->discord = $discord;
    }

    public function getUsername(): string {
        return $this->discordUser->username;
    }

    public function getAvatarUrl(): string {
        return $this->discordUser->avatar;
    }

    public function getId(): string {
        return $this->discordUser->id;
    }

    public function getMention(): string {
        DiscordPhpHelper::idToMention($this->discordUser->id);
    }

    public function createDM(): PromiseInterface {
        $discord = $this->discord;

        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->discordUser->getPrivateChannel()->then(
            function (Channel $channel) use ($discord) {
                return new DiscordPhpChannel($channel, $discord);
            }
        );
    }

    public function isBot(): bool {
        return $this->discordUser->bot;
    }

    public function getTag(): string {
        return $this->discordUser->username . '#' . $this->discordUser->discriminator;
    }
}