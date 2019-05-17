<?php

namespace Ancestor\ImageTemplate;

class ImageTemplate {
    /**
     * Width of the image that'll be put on the template.
     * @var int
     */
    public $imgW;
    /**
     * Height of the image that'll be put on the template.
     * @var int
     */
    public $imgH;
    /**
     * Height of the template.
     * @var int
     */
    public $templateH;
    /**
     * Width of the template.
     * @var int
     */
    public $templateW;
    /**
     * Where to put the image on template, X value
     * @var int
     */
    public $imgPositionX;
    /**
     * Where to put the image on template, Y value.
     * @var int
     */
    public $imgPositionY;

    /**
     * ImageTemplate constructor.
     * @param int $imgW
     * @param int $imgH
     * @param int $templateW
     * @param int $templateH
     * @param int $imgPositionX
     * @param int $imgPositionY
     */
    public function __construct(int $imgW, int $imgH, int $templateW, int $templateH, int $imgPositionX, int $imgPositionY) {
        $this->imgW = $imgW;
        $this->imgH = $imgH;
        $this->templateW = $templateW;
        $this->templateH = $templateH;
        $this->imgPositionX = $imgPositionX;
        $this->imgPositionY = $imgPositionY;
    }

}