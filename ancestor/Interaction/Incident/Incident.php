<?php

namespace Ancestor\Interaction\Incident;

use Ancestor\Interaction\AbstractInteraction;
use Ancestor\Interaction\Effect;

class Incident extends AbstractInteraction {

    /**
     * @var IncidentAction[]
     */
    public $actions;

    /**
     * @var IncidentAction|null
     */
    static private $defAction = null;

    public function defaultAction(): IncidentAction {
        if (self::$defAction === null) {
            self::$defAction = new IncidentAction();
            self::$defAction->name = 'skip';
            self::$defAction->effect = new Effect();
            /** @noinspection PhpUnhandledExceptionInspection */
            self::$defAction->effect->setDescription('You choose to walk away peacefully.');
        }
        return self::$defAction;
    }

    public function getActionIfValid(string $actionName, ?string $class = null): ?IncidentAction {
        $action = parent::getActionIfValid($actionName);
        if ($action->isAvailableForClass($class)) {
            return $action;
        }
        return null;
    }

    /**
     * @param string $commandName
     * @param null|string $class
     * @return string returnsActionList
     */
    public function getDefaultFooterText(string $commandName, ?string $class = null): string {
        if ($this->actions === null) {
            return '';
        }
        $footerText = $this->getDefaultFooterStart($commandName);
        foreach ($this->actions as $action) {
            if ($action->isAvailableForClass($class)) {
                $footerText .= mb_strtolower($action->name) . ', ';
            }
        }
        $footerText .= $this->defaultAction()->name;
        return $footerText;
    }

}