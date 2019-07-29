<?php

namespace Ancestor\Interaction;

class DirectAction {

    /**
     * @var bool Whether or not effect ISN'T used against an enemy, AKA: effect is positive
     */
    public $requiresTarget = false;

    /**
     * @var string
     * @required
     */
    public $name;

    /**
     * @var DirectActionEffect
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

    /**
     * @var DirectActionEffect|null
     */
    public $selfEffect = null;

    public function isUsableVsStealth(): bool {
        return $this->effect->removesStealth || $this->requiresTarget;
    }

}