<?php

namespace Ancestor\Interaction\Stats;

use Ancestor\Interaction\AbstractLivingBeing;
use Ancestor\Interaction\Hero;

class LightingEffect extends AbstractTypedPermanentState {
    /**
     * @var StatModifier[]
     */
    public array $monsterStatModifiers;

    public function apply(AbstractLivingBeing $subject): void {
        if ($subject instanceof Hero) {
            parent::apply($subject);
            return;
        }
        foreach ($this->monsterStatModifiers as $monsterStatModifier) {
            $subject->statManager->addModifier($monsterStatModifier);
        }
    }

    public function getDescription(): string {
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

    public function getTitle(): string {
        return 'The lighting changes... ***' . $this->name . '!***';
    }
}