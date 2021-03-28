<?php

namespace Ancestor\BotIO;

interface MessageInterface {
    public function isAuthorBot(): bool;

    public function getContent(): string;

    public function reply(string $text);

    public function replyWithEmbedImage(string $text, string $embedTitle, $embedImage);

    public function getChannel(): ChannelInterface;

    public function getAuthor() : UserInterface;

    /**
     * @return UserInterface[]
     */
    public function getUserMentions() : array;
}
