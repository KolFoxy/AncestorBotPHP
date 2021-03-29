<?php

namespace Ancestor\BotIO;

use Discord\Parts\Embed\Embed;

interface ChannelInterface {
    public function send(string $text, EmbedInterface $embed = null);

    public function sendWithSimpleEmbed(string $text, string $embedTitle, string $embedBody);

    public function sendWithFootedEmbed(string $text, string $embedTitle, string $embedBody, string $footerText, ?string $footerImage);

    public function sendWithFile(string $text, ?string $fileName, $file, EmbedInterface $embed = null);

    public function isNSFW(): bool;

    public function getName(): string;

    public function getId(): string;
}