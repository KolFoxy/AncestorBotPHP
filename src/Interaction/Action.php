<?php

namespace Ancestor\Interaction;

class Action extends AbstractAction {

    /**
     * Array of possible effects of the action.
     * @var Effect[]
     * @required
     */
    public array $effects;

    public function getRandomEffect(): Effect {
        return $this->effects[mt_rand(0, sizeof($this->effects) - 1)];
    }

}