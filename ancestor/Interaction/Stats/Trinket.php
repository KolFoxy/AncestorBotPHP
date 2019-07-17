<?php

namespace Ancestor\Interaction\Stats;

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

    public function apply() {
        parent::apply();
        foreach ($this->typeBonuses as $key => $bonus) {
            $this->host->statManager->typeBonuses[$key] = $bonus;
        }
    }

    public function remove() {
        parent::remove();
        foreach ($this->typeBonuses as $key => $bonus) {
            unset($this->host->statManager->typeBonuses[$key]);
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

}