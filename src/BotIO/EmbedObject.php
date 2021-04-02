<?php

namespace Ancestor\BotIO;

class EmbedObject implements EmbedInterface {

    public ?string $title = null;
    public ?string $description = null;
    public ?string $url = null;
    public ?string $timestamp = null;
    public ?int $color = null;

    public ?string $footerIconUrl = null;
    public ?string $footerProxyIcon = null;
    public ?string $footerText = null;

    public ?string $imageUrl = null;
    public ?string $imageHeight = null;
    public ?string $imageWidth = null;
    public ?string $imageProxy = null;

    public ?string $thumbnailUrl = null;
    public ?string $thumbnailHeight = null;
    public ?string $thumbnailWidth = null;
    public ?string $thumbnailProxy = null;

    public ?string $providerName = null;
    public ?string $providerUrl = null;

    public ?string $authorName = null;
    public ?string $authorUrl = null;
    public ?string $authorIconUrl = null;
    public ?string $authorIconProxy = null;

    public ?string $videoUrl = null;
    public ?string $videoProxy = null;
    public ?string $videoHeight = null;
    public ?string $videoWidth = null;

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

        if (count($this->fields) === 25) {
            return;
        }

        $this->fields[] = [
            'title' => $title,
            'body' => $body,
            'inline' => $inline
        ];
    }

    public function getFields(): ?array {
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


    /**
     * @return string|null
     */
    public function getTitle(): ?string {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string {
        return $this->url;
    }

    /**
     * @return string|null
     */
    public function getTimestamp(): ?string {
        return $this->timestamp;
    }

    /**
     * @return int|null
     */
    public function getColor(): ?int {
        return $this->color;
    }

    /**
     * @return string|null
     */
    public function getFooterIconUrl(): ?string {
        return $this->footerIconUrl;
    }

    /**
     * @return string|null
     */
    public function getFooterProxyIcon(): ?string {
        return $this->footerProxyIcon;
    }

    /**
     * @return string|null
     */
    public function getFooterText(): ?string {
        return $this->footerText;
    }

    /**
     * @return string|null
     */
    public function getImageUrl(): ?string {
        return $this->imageUrl;
    }

    /**
     * @return string|null
     */
    public function getImageHeight(): ?string {
        return $this->imageHeight;
    }

    /**
     * @return string|null
     */
    public function getImageWidth(): ?string {
        return $this->imageWidth;
    }

    /**
     * @return string|null
     */
    public function getImageProxy(): ?string {
        return $this->imageProxy;
    }

    /**
     * @return string|null
     */
    public function getThumbnailUrl(): ?string {
        return $this->thumbnailUrl;
    }

    /**
     * @return string|null
     */
    public function getThumbnailHeight(): ?string {
        return $this->thumbnailHeight;
    }

    /**
     * @return string|null
     */
    public function getThumbnailWidth(): ?string {
        return $this->thumbnailWidth;
    }

    /**
     * @return string|null
     */
    public function getThumbnailProxy(): ?string {
        return $this->thumbnailProxy;
    }

    /**
     * @return string|null
     */
    public function getProviderName(): ?string {
        return $this->providerName;
    }

    /**
     * @return string|null
     */
    public function getProviderUrl(): ?string {
        return $this->providerUrl;
    }

    /**
     * @return string|null
     */
    public function getAuthorName(): ?string {
        return $this->authorName;
    }

    /**
     * @return string|null
     */
    public function getAuthorUrl(): ?string {
        return $this->authorUrl;
    }

    /**
     * @return string|null
     */
    public function getAuthorIconUrl(): ?string {
        return $this->authorIconUrl;
    }

    /**
     * @return string|null
     */
    public function getAuthorIconProxy(): ?string {
        return $this->authorIconProxy;
    }

    /**
     * @return string|null
     */
    public function getVideoUrl(): ?string {
        return $this->videoUrl;
    }

    /**
     * @return string|null
     */
    public function getVideoProxy(): ?string {
        return $this->videoProxy;
    }

    /**
     * @return string|null
     */
    public function getVideoHeight(): ?string {
        return $this->videoHeight;
    }

    /**
     * @return string|null
     */
    public function getVideoWidth(): ?string {
        return $this->videoWidth;
    }

}