<?php

namespace Ancestor\Interaction\Fight;

use Ancestor\Interaction\HeroClass;
use Ancestor\Interaction\Incident\IncidentCollection\IncidentCollectionInterface;
use Ancestor\Interaction\MonsterType;

interface EncounterCollectionInterface extends IncidentCollectionInterface {

    /**
     * @return MonsterType
     */
    public function randRegularMonsterType(): MonsterType;

    /**
     * @return MonsterType
     */
    public function randEliteMonsterType(): MonsterType;

    /**
     * @return HeroClass
     */
    public function randHeroClass(): HeroClass;

}