<?php

namespace Ancestor\Interaction\Stats;

use Ancestor\Interaction\AbstractLivingBeing;

class Trinket extends AbstractPermanentState {

    const RARITY_VERY_COMMON = 0;
    const RARITY_COMMON = 1;
    const RARITY_UNCOMMON = 2;
    const RARITY_RARE = 3;
    const RARITY_VERY_RARE = 4;
    const RARITY_ANCESTRAL = 5;
    const RARITY_CRYSTALLINE = 6;
    const RARITY_TROPHY = 7;

    /**
     * @var string
     * @required
     */
    public $image;
    /**
     * @var int
     * @required
     */
    public $rarity;

    /**
     * @var string|null Name of the class that this trinket is restricted to. NULL for no restrictions.
     */
    public $classRestriction = null;

    /**
     * @var TypeBonus[]
     */
    protected $typeBonuses = [];

    /**
     * @var string|null
     */
    public $text = null;

    public function apply(AbstractLivingBeing $host) {
        parent::apply($host);
        foreach ($this->typeBonuses as $key => $bonus) {
            $host->statManager->typeBonuses[$key] = $bonus;
        }
    }

    public function remove(AbstractLivingBeing $host) {
        parent::remove($host);
        foreach ($this->typeBonuses as $key => $bonus) {
            unset($host->statManager->typeBonuses[$key]);
        }
    }

    /**
     * @param TypeBonus[] $typeBonuses
     */
    public function setTypeBonuses($typeBonuses) {
        foreach ($typeBonuses as $key => $bonus) {
            $this->typeBonuses[$this->name . $key . $bonus->type] = $bonus;
        }
    }

    /**
     * @return TypeBonus[]
     */
    public function getTypeBonuses() {
        return $this->typeBonuses;
    }

    public function getDescription(): string {
        $res = is_null($this->text) ? '' : $this->text . PHP_EOL;
        $counter = 1;
        $statModsMax = count($this->statModifiers);
        foreach ($this->statModifiers as $modifier) {
            $res .= $modifier->__toString();
            if ($counter === $statModsMax) {
                continue;
            }
            $countMod3 = $counter % 3;
            if ($countMod3 === 0) {
                $res .= PHP_EOL;
            } else {
                $res .= ' | ';
            }
            $counter++;
        }
        foreach ($this->typeBonuses as $typeBonus) {
            $res .= PHP_EOL . $typeBonus->__toString();
        }
        return $res;
    }

}