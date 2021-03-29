<?php

namespace Ancestor\Interaction\Stats;

class Trinket extends AbstractTypedPermanentState {

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
    public string $image;
    /**
     * @var int
     * @required
     */
    public int $rarity;

    /**
     * @var string|null Name of the class that this trinket is restricted to. NULL for no restrictions.
     */
    public ?string $classRestriction = null;

    /**
     * @var string|null
     */
    public ?string $text = null;


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