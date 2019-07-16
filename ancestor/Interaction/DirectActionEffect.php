<?php

namespace Ancestor\Interaction;

class DirectActionEffect extends AbstractEffect {


    /**
     * Chance of crit for the effect. negative - can't crit.
     * @var int
     */
    public $critChance = 0;

    /**
     * @var int Negative for guarantee hit
     */
    public $hitChance = 100;

    /**
     * @var \Ancestor\Interaction\Stats\TypeBonus[]|null
     */
    public $typeBonuses = null;

    /**
     * @var bool
     */
    public $ignoresArmor = false;

    /**
     * @var bool
     */
    public $removesStealth = false;

    public function canCrit(): bool {
        return isset($this->critChance);
    }

    public function applyToTarget(AbstractLivingBeing $target){
        // TODO : implement the fucking method
    }

}