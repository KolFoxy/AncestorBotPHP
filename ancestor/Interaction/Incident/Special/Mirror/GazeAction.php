<?php

namespace Ancestor\Interaction\Incident\Special\Mirror;

use Ancestor\Interaction\ActionResult\ActionResult;
use Ancestor\Interaction\Effect;
use Ancestor\Interaction\Incident\IActionSingletonInterface;
use Ancestor\Interaction\Incident\IncidentAction;
use Ancestor\Interaction\Stats\StatModifier;
use Ancestor\Interaction\Stats\Stats;

class GazeAction extends IncidentAction implements IActionSingletonInterface {
    /**
     * @var IncidentAction
     */
    protected static $instance = null;

    public static function getInstance(): IncidentAction {
        if (self::$instance === null) {
            self::$instance = new GazeAction();
        }
        return self::$instance;
    }

    protected function __construct() {
        $this->name = 'Gaze into it';
        $this->effect = new Effect();
        $this->effect->setDescription('The layer of dust prevents seeing the clear image, so you wipe it off. As soon as enough surface is clear for you to take a look, you see the unblemished image of your own eyes. You stare at them for a moment before realising that you can’t look away. You try to close your eyes but the image still stays. After long excruciating minutes of staring into your own eyes, the image finally shifts. You see yourself in the mirror. The surface is perfectly clear and you are finally able to move. The frame of the mirror and everything around you have shifted its colors to new, unnatural, maddening ones.');
        $this->effect->stress_value = 10;
    }

    protected function addTimedEffectsToResult(ActionResult $result) {
        $stats = Stats::getStatNamesArray();
        $maxStatsIndex = count($stats) - 1;
        for ($i = 0; $i < 2; $i++) {
            $statMod = new StatModifier();
            $statMod->duration = 20;
            $statMod->chance = -1;
            $statMod->value = mt_rand(-50, 50);
            $statMod->setStat($stats[mt_rand(0, $maxStatsIndex)]);
            $result->addTimedEffect($statMod);
        }
    }
}