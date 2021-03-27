<?php

namespace Ancestor\BotIO;

class EmbedObject implements EmbedInterface {
    public ?string $title;
    public ?string $description;
    public ?string $url;
    public ?string $timestamp;
    public ?int $color;

    public ?string $footerIconUrl;
    public ?string $footerProxyIcon;
    public ?string $footerText;

    public ?string $imageUrl;
    public ?string $imageHeight;
    public ?string $imageWidth;
    public ?string $imageProxy;

    public ?string $thumbnailUrl;
    public ?string $thumbnailHeight;
    public ?string $thumbnailWidth;
    public ?string $thumbnailProxy;

    public ?string $providerName;
    public ?string $providerUrl;

    public ?string $authorName;
    public ?string $authorUrl;
    public ?string $authorIconUrl;
    public ?string $authorIconProxy;

    public ?string $videoUrl;
    public ?string $videoProxy;
    public ?string $videoHeight;
    public ?string $videoWidth;

    public ?array $fields = null;


    public function addField(string $title, string $body, bool $inline = false) {
        if (is_null($this->fields)) {
            $this->fields = [];
        }
        $this->fields[] = [$title, $body, $inline];
    }

    public function getFields(): array {
        return $this->fields;
    }

    public function setFooter(string $footerText, ?string $footerIconUrl = null, ?string $footerProxyIcon = null) {
        $this->footerText = $footerText;
        $this->footerIconUrl = $footerIconUrl;
        $this->footerProxyIcon = $footerProxyIcon;
    }

}