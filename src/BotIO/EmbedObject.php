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

    /**
     * array:
     *       [
     *          [
     *            'title' => string
     *            'body' => string
     *            'inline' => bool
     *          ]
     *       ]
     * @var array|null
     */
    public ?array $fields = null;

    public function addField(string $title, string $body, bool $inline = false): void {
        if (is_null($this->fields)) {
            $this->fields = [];
        }

        $this->fields[] = [
            'title' => $title,
            'body' => $body,
            'inline' => $inline
        ];
    }

    public function getFields(): array {
        return $this->fields;
    }

    public function setFooter(string $footerText, ?string $footerIconUrl = null, ?string $footerProxyIcon = null): void {
        $this->footerText = $footerText;
        $this->footerIconUrl = $footerIconUrl;
        $this->footerProxyIcon = $footerProxyIcon;
    }

    public function setThumbnail(string $imageUrl, ?int $width = null, ?int $height = null, ?int $imageProxy = null): void {
        $this->thumbnailUrl = $imageUrl;
        $this->thumbnailWidth = $width;
        $this->thumbnailHeight = $height;
        $this->thumbnailProxy = $imageProxy;
    }


    public function setTitle(string $title): void {
        $this->title = $title;
    }

    public function setColor(int $color): void {
        $this->color = $color;
    }

    public function setDescription(string $description): void {
        $this->description = $description;
    }

    public function setImage(string $imageUrl): void {
        $this->imageUrl = $imageUrl;
    }
}