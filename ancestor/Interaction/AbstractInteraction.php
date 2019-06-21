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
     * @return Action|bool Returns TRUE if DEFAULT_ACTION
     */
    public function getActionIfValid(string $actionName) {
        $actionL = mb_strtolower($actionName);
        if ($actionL === mb_strtolower(self::defaultAction()->name)) {
            return true;
        }
        if (empty($this->actions)) {
            return false;
        }
        foreach ($this->actions as $action) {
            if (mb_strtolower($action->name) === $actionL) {
                return $action;
            }
        }
        return false;
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
        if ($this->actions === null){
            return '';
        }
        $footerText = 'Respond with "' . $commandName . ' [ACTION]" to perform the corresponding action. ' . PHP_EOL
            . 'Available actions: ';
        foreach ($this->actions as $action) {
            $footerText .= mb_strtolower($action->name) . ', ';
        }
        $footerText .= self::defaultAction()->name;
        return $footerText;
    }

    abstract public static function defaultAction(): Action;

}