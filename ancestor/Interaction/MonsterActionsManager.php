<?php

namespace Ancestor\Interaction;

class MonsterActionsManager {

    /**
     * @var array ['status1' => 'actionName1', ...]
     */
    public $statusActionsRelations = [];

    /**
     * @var null|DirectAction[]
     */
    protected $freeActions = null;

    public function getDirectAction(Monster $monster): DirectAction {
        foreach ($this->statusActionsRelations as $status => $actionName) {
            if ($monster->statManager->has($status)) {
                return $monster->type->getActionIfValid($actionName) ?? $monster->type->getRandomAction();
            }
        }
        return $this->getFreeAction($monster->type);
    }

    protected function getFreeAction(MonsterType $monsterType): DirectAction {
        if ($this->freeActions === null) {
            $this->setFreeActions($monsterType);
        }
        return $this->freeActions[mt_rand(0, count($this->freeActions) - 1)];
    }

    protected function setFreeActions(MonsterType $monsterType) {
        $this->freeActions = [];
        foreach ($monsterType->actions as $directAction) {
            if (!in_array($directAction->name, $this->statusActionsRelations)) {
                $this->freeActions[] = $directAction;
            }
        }
    }

}