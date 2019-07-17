<?php

namespace Ancestor\Interaction\Stats;

class TypeBonus {

    /**
     * @var string
     * @required
     */
    public $type;

    /**
     * @var int
     */
    public $damageMod = 0;

    /**
     * @var int
     */
    public $critChanceMod = 0;

    /**
     * @var int
     */
    public $accMod = 0;

    public function combineWith(TypeBonus $typeBonus): TypeBonus {
        $this->damageMod += $typeBonus->damageMod;
        $this->critChanceMod += $typeBonus->critChanceMod;
        $this->accMod += $typeBonus->accMod;
        return $this;
    }

}