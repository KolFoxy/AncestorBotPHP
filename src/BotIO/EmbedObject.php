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