<?php

namespace Ancestor\BotIO;

interface EmbedInterface {
    public function addField(string $title, string $body, bool $inline = false): void;

    public function getFields(): array;

    public function setFooter(string $footerText, ?string $footerIconUrl = null, ?string $footerProxyIcon = null): void;

    public function setThumbnail(string $imageUrl, ?int $width = null, ?int $height = null, ?int $imageProxy = null): void;

    public function setTitle(string $title): void;

    public function setColor(int $color): void;

    public function setDescription(string $description): void;

    public function setImage(string $imageUrl): void;
}