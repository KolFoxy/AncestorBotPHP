<?php

namespace Ancestor\BotIO\DiscordPhpBot;

use Ancestor\BotIO\ChannelInterface;
use Ancestor\BotIO\EmbedInterface;
use Ancestor\BotIO\MessageInterface;
use Ancestor\BotIO\UserInterface;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;

class DiscordPhpMessage implements MessageInterface {

    protected Discord $discord;
    protected Message $discordMessage;

    /**
     * @var DiscordPhpUser[]
     */
    protected array $mentions;

    public function __construct(Message $discordMessage, Discord $discord) {
        $this->discordMessage = $discordMessage;
        $this->discord = $discord;
    }

    public function isAuthorBot(): bool {
        return $this->getAuthor()->isBot();
    }

    public function getContent(): string {
        return $this->discordMessage->content;
    }

    public function reply(string $text, EmbedInterface $embed = null) {
        $this->discordMessage->channel->sendMessage(
            DiscordPhpHelper::idToMention($this->discordMessage->author->id) . ' ' . $text,
            false,
            $embed === null ? null : DiscordPhpHelper::embedInterfaceToDiscordEmbed($embed, $this->discord)
        );
    }

    public function replyWithEmbedImage(string $text, string $embedTitle, $embedImage) {
        $embed = new Embed($this->discord);
        $embed->title = $embedTitle;
        $embed->setImage($embedImage);
        $this->discordMessage->channel->sendMessage(DiscordPhpHelper::idToMention($this->discordMessage->author->id) . ' ' . $text, false, $embed);
    }

    public function getChannel(): ChannelInterface {
        return new DiscordPhpChannel($this->discordMessage->channel, $this->discord);
    }

    public function getAuthor(): UserInterface {
        return new DiscordPhpUser($this->discordMessage->author, $this->discord);
    }

    /**
     * @return DiscordPhpUser[]|array
     */
    public function getUserMentions(): array {
        if (!isset($this->mentions)) {
            $this->mentions = [];
            foreach ($this->discordMessage->mentions as $mention) {
                $this->mentions[] = new DiscordPhpUser($mention, $this->discord);
            }
        }
        return $this->mentions;
    }

    public function getAttachments(): array {
        return $this->discordMessage->attachments;
    }

    public function hasEmbeds(): bool {
        return !empty($this->discordMessage->embeds) || (count($this->discordMessage->embeds) > 0);
    }
}