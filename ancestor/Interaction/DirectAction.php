<?php

namespace Ancestor\Interaction;

class DirectAction {


    const TRANSFORM_ACTION = 'Transform';

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

    public function isTransformAction():bool {
        return $this->name === self::TRANSFORM_ACTION;
    }

}