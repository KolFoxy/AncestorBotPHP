<?php

namespace Ancestor\Curio;

class Curio {
    /**
     * @var string
     * @required
     */
    public $name;
    /**
     * @var string
     * @required
     */
    public $description;
    /**
     * URL/path to the image of the curio.
     * @var string
     * @required
     */
    public $image;
    /**
     * @var Action[]
     */
    public $actions;
}