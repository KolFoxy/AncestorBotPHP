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
        $this->effect->setDescription('The hammer is heavy, but it is irrelevant. You are set to start and finish this. Ignoring the man, you go to the anvil, place your dagger on top of it and then you strike it. Unnaturally, it form begins to shift. You strike again, and again, and again, and soon you are out of breath. You drop the hammer, and fall on the ground yourself next. When you finally regain breath, you notice that the man, the anvil and the smithery itself have disappeared. The only thing that is left is a giant scythe, laying on the ground before you… How is it possible that your dagger hold enough substance to be reshaped into that thing? Or was it? Does the blade of the scythe shrink when you look at it at a different angle?'
            . PHP_EOL . 'You take the scythe. It is heavy, unnaturally cold and you feel the daunting vibrations emitting from it.');
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