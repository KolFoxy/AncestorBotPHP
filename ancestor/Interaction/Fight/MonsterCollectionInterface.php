<?php

namespace Ancestor\Interaction\Fight;

use Ancestor\Interaction\MonsterType;

interface MonsterCollectionInterface {

    /**
     * @return MonsterType
     */
    public function getRandMonsterType(): MonsterType;
}