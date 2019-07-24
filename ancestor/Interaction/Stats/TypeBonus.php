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

    public function getBonusesString(): string {
        $format = '%+d';
        return
            ($this->damageMod !== 0 ? sprintf($format, $this->damageMod) . '% DMG' .
                (($this->critChanceMod !== 0 || $this->accMod !== 0) ? ',' : '.') : '')
            . ($this->critChanceMod !== 0 ? sprintf($format, $this->critChanceMod) . '% CRIT' .
                ($this->accMod !== 0 ? ',' : '.') : '')
            . ($this->accMod !== 0 ? sprintf($format, $this->accMod) . '% ACC' : '');
    }


    public function __toString() {
        return 'VS ' . Stats::formatName($this->type) . ': ' . $this->getBonusesString();
    }
}