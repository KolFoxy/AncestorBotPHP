<?php

namespace Ancestor\Interaction\Fight;

use Ancestor\Interaction\HeroClass;
use Ancestor\Interaction\MonsterType;

interface EncounterCollectionInterface {

    /**
     * @return MonsterType
     */
    public function getRandMonsterType(): MonsterType;

    /**
     * @return HeroClass
     */
    public function getRandHeroClass(): HeroClass;
}