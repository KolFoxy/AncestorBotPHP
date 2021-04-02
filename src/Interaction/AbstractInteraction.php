<?php

namespace Ancestor\Interaction;



abstract class AbstractInteraction {

    /**
     * @var string
     * @required
     */
    public string $name;
    /**
     * @var string
     * @required
     */
    public string $description;

    /**
     * URL to the image of the interaction.
     * @var string|null
     * @required
     */
    public ?string $image;

    /**
     * @var AbstractAction[]
     */
    public array $actions;

    /**
     * @param string $actionName
     * @return AbstractAction|mixed|null
     */
    public function getActionIfValid(string $actionName) {
        $actionL = mb_strtolower($actionName);
        foreach ($this->actions as $action) {
            if (mb_strpos(mb_strtolower($action->name), $actionL) !== false) {
                return $action;
            }
        }
        if (mb_strpos(mb_strtolower($this->defaultAction()->name), $actionL) !== false) {
            return $this->defaultAction();
        }
        return null;
    }


    /**
     * @param string $commandName
     * @return string returnsActionList
     */
    public function getDefaultFooterText(string $commandName): string {
        if ($this->actions === null) {
            return '';
        }
        $footerText = $this->getDefaultFooterStart($commandName);
        foreach ($this->actions as $action) {
            $footerText .= mb_strtolower($action->name) . ', ';
        }
        $footerText .= $this->defaultAction()->name;
        return $footerText;
    }

    protected function getDefaultFooterStart(string $commandName): string {
        return 'Respond with "' . $commandName . ' [ACTION]" to perform the corresponding action. ' . PHP_EOL
            . 'Available actions: ';
    }

    /**
     * @return mixed
     */
    abstract public function defaultAction();

}