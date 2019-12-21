<?php

namespace Ancestor\Interaction\Stats;

use Ancestor\Interaction\AbstractLivingBeing;
use Ancestor\Interaction\Hero;

class LightingEffect extends AbstractTypedPermanentState {
    /**
     * @var StatModifier[]
     */
    public $monsterStatModifiers;

    public function apply(AbstractLivingBeing $subject) {
        if ($subject instanceof Hero) {
            parent::apply($subject);
            return;
        }
        foreach ($this->monsterStatModifiers as $monsterStatModifier) {
            $subject->statManager->addModifier($monsterStatModifier);
        }
    }

    public function getDescription() {
        $res = 'Heroes:' . PHP_EOL;
        foreach ($this->statModifiers as $statModifier) {
            $res .= '*``' . $statModifier->__toString() . '``*' . PHP_EOL;
        }
        foreach ($this->typeBonuses as $typeBonus) {
            $res .= '*``' . $typeBonus->__toString() . '``*' . PHP_EOL;
        }
        $res .= 'Monsters:';
        foreach ($this->monsterStatModifiers as $statModifier) {
            $res .= PHP_EOL . '*``' . $statModifier->__toString() . '``*';
        }
        return $res;
    }

    public function getTitle() {
        return 'The lighting changes... ***' . $this->name . '!***';
    }
}