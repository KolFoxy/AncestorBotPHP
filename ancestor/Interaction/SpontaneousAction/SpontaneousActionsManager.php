<?php

namespace Ancestor\Interaction\SpontaneousAction;

use Ancestor\Interaction\DirectActionEffect;

class SpontaneousActionsManager {
    /**
     * @var SpontaneousAction[]
     */
    public $spontaneousActions = [];

    public function isEmpty():bool {
        return $this->spontaneousActions === [];
    }

    /**
     * @param bool $stunnedTurn
     * @return DirectActionEffect[]
     */
    public function getTurnStartEffects(bool $stunnedTurn) {
        $res = [];
        foreach ($this->spontaneousActions as $spontaneousAction) {
            if ($stunnedTurn && !$spontaneousAction->ignoresStun) {
                continue;
            }
            if (mt_rand(1, 100) <= $spontaneousAction->chance) {
                $res[] = $spontaneousAction->effect;
            }
        }
        return $res;
    }

    /**
     * @param SpontaneousAction[]|SpontaneousAction|null $toAdd
     */
    public function addSpontaneousAction($toAdd) {
        if ($toAdd === null) {
            return;
        }
        if (is_array($toAdd)) {
            $this->spontaneousActions = array_merge($this->spontaneousActions, $toAdd);
            return;
        }
        $this->spontaneousActions[] = $toAdd;
    }

    /**
     * @param SpontaneousAction[]|SpontaneousAction|null $toRemove
     */
    public function removeSpontaneousAction($toRemove) {
        if ($toRemove === null) {
            return;
        }
        if (is_array($toRemove)) {
            foreach ($toRemove as $item) {
                if (($key = array_search($item, $this->spontaneousActions)) !== false) {
                    unset($this->spontaneousActions[$key]);
                }
            }
            return;
        }
        if (($key = array_search($toRemove, $this->spontaneousActions)) !== false) {
            unset($this->spontaneousActions[$key]);
        }
    }

    /**
     * SpontaneousActionsManager constructor.
     * @param SpontaneousAction[]|null $spontaneousActions
     */
    public function __construct($spontaneousActions) {
        if ($spontaneousActions === null) {
            return;
        }
        $this->spontaneousActions = $spontaneousActions;
    }
}