<?php

namespace Ancestor\Interaction;

use Ancestor\Interaction\Stats\StatusEffect;

class MonsterType extends AbstractLivingInteraction {

    /**
     * @var DirectAction|null
     */
    private ?DirectAction $defAction = null;

    /**
     * @var StatusEffect[]|null
     */
    public ?array $startingStatusEffects = null;

    /**
     * @var null|MonsterActionsManager
     */
    public ?MonsterActionsManager $actionsManager = null;

    public function defaultAction(): DirectAction {
        if ($this->defAction === null) {
            $action = new DirectAction();
            $action->name = 'pass turn';
            $action->requiresTarget = true;
            $effect = new DirectActionEffect();
            /** @noinspection PhpUnhandledExceptionInspection */
            $effect->setDescription($this->name . ' passed the turn.');
            $effect->hitChance = -1;
            $effect->critChance = -1;
            $action->effect = $effect;
            $this->defAction = $action;
        }
        return $this->defAction;
    }

    public function getActionIfValid(string $actionName): ?DirectAction {
        return parent::getActionIfValid($actionName);
    }

    /**
     * @param array|null $statusActionsRelations ['status1' => 'actionName', ...]
     */
    public function setActionsManager(?array $statusActionsRelations): void {
        if ($statusActionsRelations === null) {
            return;
        }
        $this->actionsManager = new MonsterActionsManager($statusActionsRelations, $this);
    }
}