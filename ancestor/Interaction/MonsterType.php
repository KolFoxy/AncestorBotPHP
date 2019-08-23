<?php

namespace Ancestor\Interaction;

class MonsterType extends AbstractLivingInteraction {

    /**
     * @var DirectAction|null
     */
    private $defAction = null;

    /**
     * @var \Ancestor\Interaction\Stats\StatusEffect[]|null
     */
    public $startingStatusEffects = null;

    /**
     * @var null|MonsterActionsManager
     */
    public $actionsManager = null;

    public function defaultAction(): DirectAction {
        if ($this->defAction === null) {
            $action = new DirectAction();
            $action->name = 'pass turn';
            $action->requiresTarget = true;
            $effect = new DirectActionEffect();
            /** @noinspection PhpUnhandledExceptionInspection */
            $effect->setDescription($this->name.' passed the turn.');
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
}