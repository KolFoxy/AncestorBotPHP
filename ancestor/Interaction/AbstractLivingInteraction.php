<?php

namespace Ancestor\Interaction;

use Ancestor\CommandHandler\CommandHelper;

abstract class AbstractLivingInteraction extends AbstractInteraction {

    /**
     * @var string[]
     */
    public $types = [''];

    /**
     * @var int
     */
    public $healthMax = 35;

    /**
     * @var DirectAction[]
     */
    public $actions;

    /**
     * @var array|null
     */
    public $actionRatings = null;

    /**
     * @var array
     */
    public $stats = [];

    /**
     * @var DirectAction|null
     */
    public $riposteAction = null;

    public function getDefaultFooterText(string $commandName, bool $vsStealth = false, bool $noTransform = false): string {
        $footerText = 'React with action number or respond with "' . $commandName . ' [ACTION]" to perform the corresponding action. ' . PHP_EOL
            . 'Available actions: ';
        foreach ($this->actions as $key => $action) {
            if ($noTransform && $action->isTransformAction()) {
                continue;
            }
            if ($vsStealth && !$action->isUsableVsStealth()) {
                continue;
            }
            $footerText .= (string)CommandHelper::numberToEmoji($key + 1) . mb_strtolower($action->name) . ', ';
        }
        return $footerText . CommandHelper::numberToEmoji(0) . $this->defaultAction()->name;
    }

    /**
     * @param bool $vsStealth
     * @param bool $noTransform
     * @return string[]
     */
    public function getAvailableActionsEmojis(bool $vsStealth = false, bool $noTransform = false): array {
        $res = [];
        foreach ($this->actions as $key => $action) {
            if ($noTransform && $action->isTransformAction()) {
                continue;
            }
            if ($vsStealth && !$action->isUsableVsStealth()) {
                continue;
            }
            $em = CommandHelper::numberToEmoji($key + 1);
            if ($em !== null) {
                $res[] = $em;
            }
        }
        $res[] = CommandHelper::numberToEmoji(0);
        return $res;
    }

    /**
     * @param bool $targetStealthed
     * @return DirectAction
     */
    public function getRandomAction(bool $targetStealthed = false): DirectAction {
        if ($targetStealthed) {
            return $this->getActionVsStealthed();
        }
        if ($this->actionRatings !== null) {
            return $this->getRatedAction();
        }
        return $this->actions[mt_rand(0, sizeof($this->actions) - 1)];
    }

    public function getActionVsStealthed() {
        $pool = [];
        foreach ($this->actions as $action) {
            if ($action->effect->removesStealth) {
                return $action;
            }
            if ($action->isUsableVsStealth()) {
                $pool[] = $action;
            }
        }
        if (($count = count($pool)) === 0) {
            return $this->defaultAction();
        }
        return $pool[mt_rand(0, $count - 1)];
    }

    /**
     * @return DirectAction
     */
    public abstract function defaultAction();

    protected function getRatedAction(): DirectAction {
        $totalValue = array_sum($this->actionRatings);
        $seed = mt_rand(1, $totalValue);
        foreach ($this->actionRatings as $actionName => $rating) {
            $seed -= $rating;
            if ($seed <= 0) {
                return $this->getActionIfValid($actionName);
            }
        }
        return $this->getRatedAction();
    }
}