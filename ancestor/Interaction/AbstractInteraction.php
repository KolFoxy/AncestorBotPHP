<?php

namespace Ancestor\Interaction;

use CharlotteDunois\Yasmin\Models\MessageEmbed;

abstract class AbstractInteraction {

    /**
     * @var string
     * @required
     */
    public $name;
    /**
     * @var string
     * @required
     */
    public $description;
    /**
     * URL to the image of the interaction.
     * @var string
     * @required
     */
    public $image;
    /**
     * @var Action[]
     */
    public $actions;

    /**
     * @param string $actionName
     * @return Action|DirectAction|null
     */
    public function getActionIfValid(string $actionName) {
        $actionL = mb_strtolower($actionName);
        if (mb_strpos(mb_strtolower($this->defaultAction()->name), $actionL !== false)) {
            return $this->defaultAction();
        }
        foreach ($this->actions as $action) {
            if (mb_strpos(mb_strtolower($action->name), $actionL) !== false) {
                return $action;
            }
        }
        return null;
    }

    /**
     * @param string $commandName
     * @return MessageEmbed
     */
    abstract public function getEmbedResponse(string $commandName): MessageEmbed;

    /**
     * @param string $commandName
     * @return string returnsActionList
     */
    public function getDefaultFooterText(string $commandName): string {
        if ($this->actions === null) {
            return '';
        }
        $footerText = 'Respond with "' . $commandName . ' [ACTION]" to perform the corresponding action. ' . PHP_EOL
            . 'Available actions: ';
        foreach ($this->actions as $action) {
            $footerText .= mb_strtolower($action->name) . ', ';
        }
        $footerText .= $this->defaultAction()->name;
        return $footerText;
    }

    /**
     * @return mixed
     */
    abstract public function defaultAction();

}