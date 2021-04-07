<?php

namespace Ancestor\Interaction;

class MonsterActionsManager {

    /**
     * @var MonsterType
     */
    protected MonsterType $monsterType;

    /**
     * @var DirectAction[] ['status1' => DirectAction, ...]
     */
    protected array $statusActionsRelations = [];

    /**
     * MonsterActionsManager constructor.
     * @param array $statusActionsRelations ['status1' => 'actionName', ...]
     * @param MonsterType $type
     */
    public function __construct(array $statusActionsRelations, MonsterType $type) {
        $this->monsterType = $type;
        foreach ($statusActionsRelations as $status => $actionName) {
            $action = $type->getActionIfValid($actionName);
            if ($action !== null) {
                $this->statusActionsRelations[$status] = $action;
            }
        }
        $type->actions = array_values(array_diff($type->actions, $this->statusActionsRelations));
    }

    public function getDirectAction(Monster $monster, bool $targetIsStealthed = false): DirectAction {
        foreach ($this->statusActionsRelations as $status => $action) {
            if ($monster->statManager->has($status)) {
                return $action;
            }
        }
        return $this->monsterType->getRandomAction($targetIsStealthed);
    }
}