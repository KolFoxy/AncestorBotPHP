<?php

namespace Ancestor\Interaction;

use Ancestor\Interaction\Stats\StatModifier;
use Ancestor\Interaction\Stats\StatusEffect;

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

    /**
     * @var StatusEffect[]|null
     */
    public $statusEffects = null;

    /**
     * @var StatModifier[]|null
     */
    public $statModifiers = null;

}