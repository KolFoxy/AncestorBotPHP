<?php

namespace Ancestor\Interaction;

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

    public function getRandomEffect(): Effect {
        return $this->effects[mt_rand(0, sizeof($this->effects) - 1)];
    }
}