<?php

namespace Ancestor\Interaction\Incident\Special\BlindSmith;

use Ancestor\Interaction\Effect;
use Ancestor\Interaction\Hero;
use Ancestor\Interaction\Incident\IncidentAction;
use Ancestor\Interaction\Stats\StatModifier;
use Ancestor\Interaction\Stats\Stats;

class UseTheHammerAction extends IncidentAction {
    const MODIFIERS_UNIQUE_CHANCE_ID = -5861823;

    public function __construct() {
        $this->name = 'Use the hammer';
        $this->effect = new Effect();
        $this->effect->setDescription('Test, change later');
        $this->statModifiers = [];

        $statMod = new StatModifier();
        $statMod->setStat(Stats::DODGE);
        $statMod->value = -10;
        $statMod->chance = -1;
        $this->statModifiers[] = $statMod;

        $statMod = new StatModifier();
        $statMod->setStat(Stats::ACC_MOD);
        $statMod->value = -10;
        $statMod->chance = self::MODIFIERS_UNIQUE_CHANCE_ID;
        $statMod->duration = 20;
        $this->statModifiers[] = $statMod;

        $statMod = new StatModifier();
        $statMod->setStat(Stats::DAMAGE_MOD);
        $statMod->value = 300;
        $statMod->chance = self::MODIFIERS_UNIQUE_CHANCE_ID;
        $statMod->duration = 20;
        $this->statModifiers[] = $statMod;

        $statMod = new StatModifier();
        $statMod->setStat(Stats::CRIT_CHANCE);
        $statMod->value = 5;
        $statMod->chance = self::MODIFIERS_UNIQUE_CHANCE_ID;
        $statMod->duration = 20;
        $this->statModifiers[] = $statMod;
    }

    protected function applyEffectsGetResults(Hero $hero): string {
        foreach ($hero->statManager->modifiers as $key => $modifier) {
            if ($modifier->chance === self::MODIFIERS_UNIQUE_CHANCE_ID) {
                unset($hero->statManager->modifiers[$key]);
            }
        }
        return parent::applyEffectsGetResults($hero);
    }
}