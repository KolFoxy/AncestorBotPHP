<?php
/**
 * Created by PhpStorm.
 * User: KolBrony
 * Date: 19.06.2019
 * Time: 12:37
 */

namespace Ancestor\Interaction;

use CharlotteDunois\Yasmin\Models\MessageEmbed;

abstract class AbstractInteraction {

    const DEFAULT_ACTION = 'nothing';

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
        if ($actionL === mb_strtolower(self::DEFAULT_ACTION)) {
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

}