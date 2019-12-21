<?php

namespace Ancestor\Interaction\SpontaneousAction;

class SpontaneousAction {
    /**
     * @var \Ancestor\Interaction\DirectActionEffect
     * @required
     */
    public $effect;

    /**
     * @var int
     * @required
     */
    public $chance = 100;

    /**
     * @var bool
     */
    public $ignoresStun = false;
}