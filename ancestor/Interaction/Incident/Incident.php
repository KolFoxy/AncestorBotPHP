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
     * @var bool
     */
    public $disableDefAction = false;

    /**
     * @var IncidentAction|null
     */
    static private $defAction = null;

    /**
     * @param mixed $actions
     * @throws \JsonMapper_Exception
     */
    public function setActions($actions): void {
        if ($actions === null) {
            return;
        }
        if ($actions instanceof IncidentAction) {
            $this->actions = [$actions];
            return;
        }

        $mapper = new \JsonMapper();
        $mapper->bExceptionOnMissingData = true;
        $mapper->bExceptionOnUndefinedProperty = true;
        if (is_array($actions)) {
            $this->actions = [];
            foreach ($actions as $action) {
                if ($action instanceof IncidentAction) {
                    $this->actions[] = $action;
                    continue;
                }
                if (is_string($action)) {
                    $path = dirname(__DIR__, 3) . $action;
                    if (!file_exists($path)) {
                        throw new \Exception('ERROR: File "' . $path . '" doesn\'t exist.)');
                    }
                    if (mb_substr($path, -4) === '.php') {
                        $this->actions[] = require($path);
                        continue;
                    }
                    $this->actions[] = $mapper->map(json_decode(file_get_contents($path)), new IncidentAction());
                    continue;
                }
                if (is_object($action)) {
                    $this->actions[] = $mapper->map($action, new IncidentAction());
                    continue;
                }
            }
        }
    }

    /**
     * @return IncidentAction[]
     */
    public function getActions(): array {
        return $this->actions;
    }

    public function defaultAction(): IncidentAction {
        if (self::$defAction === null) {
            self::$defAction = new IncidentAction();
            self::$defAction->name = 'run';
            self::$defAction->effect = new Effect();
            /** @noinspection PhpUnhandledExceptionInspection */
            self::$defAction->effect->setDescription('You can feel this is going south.');
        }
        return self::$defAction;
    }

    public function getActionIfValid(string $actionName, ?string $class = null): ?IncidentAction {
        $action = parent::getActionIfValid($actionName);
        if ($this->disableDefAction && ($action === $this->defaultAction())) {
            return null;
        }
        if (!is_null($action) && $action->isAvailableForClass($class)) {
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
        if ($this->disableDefAction) {
            return mb_substr($footerText, 0, mb_strlen($footerText) - 2);
        }
        $footerText .= $this->defaultAction()->name;
        return $footerText;
    }

}