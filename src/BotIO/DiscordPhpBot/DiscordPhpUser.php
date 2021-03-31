<?php

namespace Ancestor\BotIO\DiscordPhpBot;

use Ancestor\BotIO\UserInterface;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\User\Member;
use Discord\Parts\User\User;
use React\Promise\PromiseInterface;

class DiscordPhpUser implements UserInterface {

    protected User $discordUser;
    protected Discord $discord;

    /**
     * DiscordPhpUser constructor.
     * @param User|Member $discordUser
     * @param Discord $discord
     */
    public function __construct($discordUser, Discord $discord) {
        $user = is_a($discordUser, Member::class) ? $discordUser->user : $discordUser;
        $this->discordUser = $user;
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
        return DiscordPhpHelper::idToMention($this->discordUser->id);
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
        return is_null($this->discordUser->bot) || $this->discordUser->bot === false  ? false : true ;
    }

    public function getTag(): string {
        return $this->discordUser->username . '#' . $this->discordUser->discriminator;
    }
}