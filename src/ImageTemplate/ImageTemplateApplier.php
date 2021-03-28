<?php

namespace Ancestor\ImageTemplate;

class ImageTemplateApplier {
    /**
     * @var ImageTemplate
     */
    public ImageTemplate $imgTemplate;

    public function __construct(ImageTemplate $imageTemplate) {
        $this->imgTemplate = $imageTemplate;
    }

    /**
     * Creates new GD image resources with $imageSrc added behind $imageDestination.
     * @param resource $imageSrc
     * @param resource $imageDestination
     * @param bool $destroySourceImages
     * @return resource
     */
    public function applyTemplate($imageSrc, $imageDestination, $destroySourceImages = false) {
        $canvas = imagecreatetruecolor($this->imgTemplate->templateW, $this->imgTemplate->templateH);
        imagecopyresized(
            $canvas,
            $imageSrc,
            $this->imgTemplate->imgPositionX,
            $this->imgTemplate->imgPositionY,
            0,
            0,
            $this->imgTemplate->imgW,
            $this->imgTemplate->imgH,
            imagesx($imageSrc),
            imagesy($imageSrc)
        );
        if ($destroySourceImages) {
            imagedestroy($imageSrc);
        }
        imagecopy(
            $canvas,
            $imageDestination,
            0,
            0,
            0,
            0,
            $this->imgTemplate->templateW,
            $this->imgTemplate->templateH
        );
        if ($destroySourceImages) {
            imagedestroy($imageDestination);
        }
        return $canvas;
    }

    /**
     * Creates new GD image resources with $imageSrc added to $imageDestination.
     * @param resource $imageSrc Image to slap.
     * @param resource $imageDestination Image to slap $imageSrc to.
     * @param bool $destroySource
     */
    public function slapTemplate($imageSrc, $imageDestination, bool $destroySource = false) {
        imagecopyresized(
            $imageDestination,
            $imageSrc,
            $this->imgTemplate->imgPositionX,
            $this->imgTemplate->imgPositionY,
            0,
            0,
            $this->imgTemplate->imgW,
            $this->imgTemplate->imgH,
            imagesx($imageSrc),
            imagesy($imageSrc)
        );
        if ($destroySource) {
            imagedestroy($imageSrc);
        }
    }
}