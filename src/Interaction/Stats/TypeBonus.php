<?php

namespace Ancestor\Interaction\Stats;

class TypeBonus {

    /**
     * @var string
     * @required
     */
    public string $type;

    /**
     * @var int
     */
    public int $damageMod = 0;

    /**
     * @var int
     */
    public int $critChanceMod = 0;

    /**
     * @var int
     */
    public int $accMod = 0;

    const SIGNED_DECIMAL_FORMAT = '%+d';

    public function combineWith(TypeBonus $typeBonus): TypeBonus {
        $this->damageMod += $typeBonus->damageMod;
        $this->critChanceMod += $typeBonus->critChanceMod;
        $this->accMod += $typeBonus->accMod;
        return $this;
    }

    public function getBonusesString(): string {
        return
            ($this->damageMod !== 0 ? sprintf(self::SIGNED_DECIMAL_FORMAT, $this->damageMod) . '% DMG' .
                (($this->critChanceMod !== 0 || $this->accMod !== 0) ? ',' : '.') : '')
            . ($this->critChanceMod !== 0 ? sprintf(self::SIGNED_DECIMAL_FORMAT, $this->critChanceMod) . '% CRIT' .
                ($this->accMod !== 0 ? ',' : '.') : '')
            . ($this->accMod !== 0 ? sprintf(self::SIGNED_DECIMAL_FORMAT, $this->accMod) . '% ACC' : '');
    }


    public function __toString() {
        return 'VS ' . Stats::formatName($this->type) . ': ' . $this->getBonusesString();
    }
}