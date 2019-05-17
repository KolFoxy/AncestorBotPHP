<?php

namespace Ancestor\Curio;

class Action {
    /**
     * @var string
     * @required
     */
    public $name;

    /**
     * Array of possible effects of the action.
     * @var Effect[]
     * @required
     */
    public $effects;
}