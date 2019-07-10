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

    /**
     * @var \Ancestor\Interaction\Stats\StatusEffect[]|null
     */
    public $statusEffects = null;

    /**
     * @var \Ancestor\Interaction\Stats\StatModifier[]|null
     */
    public $statModifiers = null;

}