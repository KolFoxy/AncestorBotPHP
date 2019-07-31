<?php

namespace Ancestor\Interaction;

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
     * @var array
     */
    public $stats = [];

    /**
     * @var DirectAction|null
     */
    public $riposteAction = null;

    public function getDefaultFooterText(string $commandName, bool $vsStealth = false, bool $noTransform = false): string {
        if (!$vsStealth && !$noTransform) {
            return parent::getDefaultFooterText($commandName);
        }
        $footerText = 'Respond with "' . $commandName . ' [ACTION]" to perform the corresponding action. ' . PHP_EOL
            . 'Available actions: ';
        foreach ($this->actions as $action) {
            if ($noTransform && $action->isTransformAction()) {
                continue;
            }
            if ($vsStealth && !$action->isUsableVsStealth()) {
                continue;
            }
            $footerText .= mb_strtolower($action->name) . ', ';
        }
        return $footerText . $this->defaultAction()->name;
    }

    /**
     * @return DirectAction
     */
    public abstract function defaultAction();
}