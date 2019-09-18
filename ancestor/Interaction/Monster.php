<?php

namespace Ancestor\Interaction;

use Ancestor\RandomData\RandomDataProvider;

class Monster extends AbstractLivingBeing {

    /**
     * @var MonsterType
     */
    public $type;

    public function __construct(MonsterType $monsterType) {
        parent::__construct($monsterType);
        if ($monsterType->startingStatusEffects === null) {
            return;
        }
        foreach ($monsterType->startingStatusEffects as $effect) {
            $this->statManager->addStatusEffect($effect->clone());
        }
    }

    public function getDeathQuote(): string {
        return RandomDataProvider::GetInstance()->GetRandomMonsterDeathQuote();
    }

    public function addHealth(int $value) {
        $this->currentHealth += $value;
        if ($this->currentHealth > $this->healthMax) {
            $this->currentHealth = $this->healthMax;
        }
    }

    /**
     * @param AbstractLivingBeing|Hero $target
     * @param DirectAction|null $action
     * @return array
     */
    public function getTurn(AbstractLivingBeing $target, ?DirectAction $action = null): array {
        if ($action === null) {
            if ($target->isStealthed()) {
                $action = $this->type->getActionVsStealthed();
            } else {
                $action = $this->getProgrammableAction();
            }
        }
        if ($action->requiresTarget) {
            $target = $this;
        }
        $heroStressStateChecker = is_a($target, Hero::class) && is_null($target->getStressState());
        $res = parent::getTurn($target, $action);
        if ($heroStressStateChecker && !is_null($target->getStressState())) {
            $res[] = $target->getStressState()->toField($target);
        }
        return $res;
    }

    public function getProgrammableAction(): DirectAction {
        if (!is_null($this->type->actionsManager)) {
            return $this->type->actionsManager->getDirectAction($this);
        }
        return $this->type->getRandomAction();
    }

}