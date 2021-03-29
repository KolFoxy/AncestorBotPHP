<?php

namespace Ancestor\BotIO;

interface MessageInterface {
    public function isAuthorBot(): bool;

    public function getContent(): string;

    public function reply(string $text, EmbedInterface $embed = null);

    public function replyWithEmbedImage(string $text, string $embedTitle, $embedImage);

    public function getChannel(): ChannelInterface;

    public function getAuthor(): UserInterface;

    /**
     * @return UserInterface[]
     */
    public function getUserMentions(): array;

    public function getAttachments(): array;

    public function hasEmbeds(): bool;
}
