<?php

namespace Ancestor\ImageTemplate;

class ImageTemplate {
    /**
     * Width of the image that'll be put on the template.
     * @var int
     */
    public int $imgW;
    /**
     * Height of the image that'll be put on the template.
     * @var int
     */
    public int $imgH;
    /**
     * Height of the template.
     * @var int
     */
    public int $templateH;
    /**
     * Width of the template.
     * @var int
     */
    public int $templateW;
    /**
     * Where to put the image on template, X value
     * @var int
     */
    public int $imgPositionX;
    /**
     * Where to put the image on template, Y value.
     * @var int
     */
    public int $imgPositionY;


}