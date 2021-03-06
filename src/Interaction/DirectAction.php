<?php

namespace Ancestor\Interaction;

use Ancestor\Interaction\Stats\StatModifier;
use Ancestor\Interaction\Stats\StatusEffect;

class DirectAction extends AbstractAction {


    const TRANSFORM_ACTION = 'Transform';

    /**
     * @var bool Whether or not effect ISN'T used against an enemy, AKA: effect is positive
     */
    public bool $requiresTarget = false;

    /**
     * @var DirectActionEffect
     * @required
     */
    public DirectActionEffect $effect;

    /**
     * @var \Ancestor\Interaction\Stats\StatusEffect[]|null
     */
    public ?array $statusEffects = null;

    /**
     * @var \Ancestor\Interaction\Stats\StatModifier[]|null
     */
    public ?array $statModifiers = null;

    /**
     * @var DirectActionEffect|null
     */
    public ?DirectActionEffect $selfEffect = null;

    public function isUsableVsStealth(): bool {
        return $this->effect->removesStealth || $this->requiresTarget;
    }

    public function isTransformAction(): bool {
        return $this->name === self::TRANSFORM_ACTION;
    }

    public function __toString() {
        $res = $this->effect->__toString();
        if ($this->statusEffects !== null || $this->statModifiers !== null) {
            $res .= PHP_EOL . 'Effects:';
            if ($this->statModifiers !== null) {
                foreach ($this->statModifiers as $statModifier) {
                    $res .= PHP_EOL . $statModifier->__toString();
                    if ($statModifier->targetSelf || $this->requiresTarget) {
                        $res .= ' on self';
                    }
                }
            }
            if ($this->statusEffects !== null) {
                foreach ($this->statusEffects as $statusEffect) {
                    $res .= PHP_EOL . $statusEffect->getShortDescription();
                    if ($statusEffect->targetSelf || $this->requiresTarget) {
                        $res .= ' on self';
                    }
                }
            }
        }
        if ($this->selfEffect !== null) {
            $res .= PHP_EOL . 'Self:' . $this->selfEffect->__toString();
        }
        return $res;
    }

}