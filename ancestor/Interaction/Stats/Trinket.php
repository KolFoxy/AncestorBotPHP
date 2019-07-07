<?php

namespace Ancestor\Interaction\Stats;

class Trinket {

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
    public $name;
    /**
     * @var string
     * @required
     */
    public $image;
    /**
     * @var int
     */
    public $rarity = 1;
    /**
     * @var StatModifier[]
     * @required
     */
    public $modifiers;
}