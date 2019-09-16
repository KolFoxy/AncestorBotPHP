<?php

namespace Ancestor\Interaction\Incident\Special\SwarmOfPeasants;

use Ancestor\Interaction\Effect;
use Ancestor\Interaction\Hero;
use Ancestor\Interaction\Incident\IActionSingletonInterface;
use Ancestor\Interaction\Incident\IncidentAction;
use Ancestor\Interaction\Stats\StatModifier;
use Ancestor\Interaction\Stats\Stats;
use CharlotteDunois\Yasmin\Models\MessageEmbed;

class ThrowTrinketAction extends IncidentAction implements IActionSingletonInterface {

    /**
     * @var IncidentAction
     */
    protected static $instance = null;
    protected const NO_TRINKET_NAME = '*You fake-throw your weapon into the crowd.*';
    protected const ANTIQUARIAN_NAME = '*You throw a random bauble you found into the crowd.*';

    public static function getInstance(): IncidentAction {
        if (self::$instance === null) {
            self::$instance = new ThrowTrinketAction();
        }
        return self::$instance;
    }


    protected function __construct() {
        $this->name = 'Throw a trinket';
        $this->effect = new Effect();
        $this->effect->setDescription('They run for it. Like wild dogs to a bone; stepping on each other and fighting for it with weapons, instruments and teeth. The pathetic brawl gives you just enough time to escape.');

        $stressModBuff = new StatModifier();
        $stressModBuff->setStat(Stats::STRESS_MOD);
        $stressModBuff->value = -5;
        $stressModBuff->chance = -1;
        $stressModBuff->duration = 10;

        $dodgeBuff = new StatModifier();
        $dodgeBuff->setStat(Stats::DODGE);
        $dodgeBuff->value = 5;
        $dodgeBuff->chance = -1;
        $dodgeBuff->duration = 10;

        $this->statModifiers = [$stressModBuff, $dodgeBuff];
    }

    public function getResult(Hero $hero, MessageEmbed $res): MessageEmbed {
        $altTitle = null;
        if ($this->heroIsAntiquarian($hero)) {
            $altTitle = self::ANTIQUARIAN_NAME;
        } elseif (!$this->heroHasTrinkets($hero)) {
            $altTitle = self::NO_TRINKET_NAME;
        }
        $res = parent::getResult($hero, $res);
        if ($altTitle !== null) {
            $res->setTitle($altTitle);
        }
        return $res;
    }

    protected function heroHasTrinkets(Hero $hero): bool {
        return $hero->getFirstTrinket() !== null || $hero->getSecondTrinket() !== null;
    }

    protected function heroIsAntiquarian(Hero $hero): bool {
        return mb_strtolower($hero->type->name) === 'antiquarian';
    }

    protected function applyEffectsGetResults(Hero $hero): string {
        if ($this->heroIsAntiquarian($hero)) {
            return parent::applyEffectsGetResults($hero);
        }
        $equippedTrinkets = [];
        $trinket = $hero->getFirstTrinket();
        if ($trinket !== null) {
            $equippedTrinkets[] = $trinket->name;
        }
        $trinket = $hero->getSecondTrinket();
        if ($trinket !== null) {
            $equippedTrinkets[] = $trinket->name;
        }
        $counter = count($equippedTrinkets);
        if ($counter === 0) {
            return '';
        }
        $trinketName = $equippedTrinkets[mt_rand(0, $counter - 1)];
        $hero->removeTrinket($trinketName);
        return '``Lost trinket:`` **``' . $trinketName . '``**';
    }


}