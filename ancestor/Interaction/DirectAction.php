<?php

namespace Ancestor\Interaction;

class DirectAction {
    /**
     * @var bool
     */
    public $requiresTarget = false;

    /**
     * @var string
     * @required
     */
    public $name;

    /**
     * @var Effect
     * @required
     */
    public $effect;

}