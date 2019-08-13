<?php

namespace Ancestor\Interaction;

use Ancestor\RandomData\RandomDataProvider;
use CharlotteDunois\Yasmin\Models\MessageEmbed;

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

    /**
     * @param string $commandName
     * @param Action[]|null $userActions
     * @return MessageEmbed
     */
    public function getEmbedResponse(string $commandName, array $userActions = null): MessageEmbed {
        return $this->type->getEmbedResponse($commandName, $userActions, $this->getHealthStatus());
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
            $action = $this->getProgrammableAction();
        }
        if ($action->requiresTarget) {
            $target = $this;
        }
        $heroStressStateChecker = is_a($target, Hero::class) && is_null($target->getStressState());
        $res = parent::getTurn($target, $action);
        if ($heroStressStateChecker && !is_null($target->getStressState())) {
            $res[] = $target->getStressState()->toField();
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