<?php

namespace Ancestor\Interaction\Fight;

use Ancestor\Interaction\HeroClass;
use Ancestor\Interaction\Incident\Incident;
use Ancestor\Interaction\MonsterType;

interface EncounterCollectionInterface {

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

    /**
     * @return Incident
     */
    public function randIncident(): Incident;
}