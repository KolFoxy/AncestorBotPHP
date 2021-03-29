<?php

namespace Ancestor\Interaction\SpontaneousAction;

use Ancestor\Interaction\DirectActionEffect;

class SpontaneousAction {
    /**
     * @var DirectActionEffect
     * @required
     */
    public DirectActionEffect $effect;

    /**
     * @var int
     * @required
     */
    public int $chance = 100;

    /**
     * @var bool
     */
    public bool $ignoresStun = false;
}