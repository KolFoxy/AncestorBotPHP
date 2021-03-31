<?php

namespace Ancestor\BotIO;

interface EmbedInterface {
    public function addField(string $title, string $body, bool $inline = false): void;

    public function getFields(): ?array;

    public function setFooter(string $footerText, ?string $footerIconUrl = null, ?string $footerProxyIcon = null): void;

    public function setThumbnail(string $imageUrl, ?int $width = null, ?int $height = null, ?int $imageProxy = null): void;

    public function setTitle(string $title): void;

    public function setColor(int $color): void;

    public function setDescription(string $description): void;

    public function setImage(string $imageUrl): void;


    /**
     * @return string|null
     */
    public function getTitle(): ?string;

    /**
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * @return string|null
     */
    public function getUrl(): ?string;

    /**
     * @return string|null
     */
    public function getTimestamp(): ?string;

    /**
     * @return int|null
     */
    public function getColor(): ?int;

    /**
     * @return string|null
     */
    public function getFooterIconUrl(): ?string;

    /**
     * @return string|null
     */
    public function getFooterProxyIcon(): ?string;

    /**
     * @return string|null
     */
    public function getFooterText(): ?string;

    /**
     * @return string|null
     */
    public function getImageUrl(): ?string;

    /**
     * @return string|null
     */
    public function getImageHeight(): ?string;

    /**
     * @return string|null
     */
    public function getImageWidth(): ?string;

    /**
     * @return string|null
     */
    public function getImageProxy(): ?string;

    /**
     * @return string|null
     */
    public function getThumbnailUrl(): ?string;

    /**
     * @return string|null
     */
    public function getThumbnailHeight(): ?string;

    /**
     * @return string|null
     */
    public function getThumbnailWidth(): ?string;

    /**
     * @return string|null
     */
    public function getThumbnailProxy(): ?string;

    /**
     * @return string|null
     */
    public function getProviderName(): ?string;

    /**
     * @return string|null
     */
    public function getProviderUrl(): ?string;

    /**
     * @return string|null
     */
    public function getAuthorName(): ?string;

    /**
     * @return string|null
     */
    public function getAuthorUrl(): ?string;

    /**
     * @return string|null
     */
    public function getAuthorIconUrl(): ?string;

    /**
     * @return string|null
     */
    public function getAuthorIconProxy(): ?string;

    /**
     * @return string|null
     */
    public function getVideoUrl(): ?string;

    /**
     * @return string|null
     */
    public function getVideoProxy(): ?string;

    /**
     * @return string|null
     */
    public function getVideoHeight(): ?string;

    /**
     * @return string|null
     */
    public function getVideoWidth(): ?string;
}