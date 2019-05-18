<?php

namespace Ancestor\Curio;

const DEFAULT_EMBED_COLOR = 13632027;
const DEFAULT_ACTION = 'nothing';
use CharlotteDunois\Yasmin\Models\MessageEmbed;

class Curio {
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
     * URL to the image of the curio.
     * @var string
     * @required
     */
    public $image;
    /**
     * @var Action[]
     */
    public $actions;

    /**
     * @param string $commandName
     * @return MessageEmbed
     */
    public function getEmbedResponse(string $commandName): MessageEmbed {
        $embedResponse = new MessageEmbed();
        $embedResponse->setThumbnail($this->image);
        $embedResponse->setTitle('**You encounter ' . $this->name . '**');
        $embedResponse->setColor(DEFAULT_EMBED_COLOR);
        $embedResponse->setDescription('*' . $this->description . '*');
        if (!empty($this->actions)) {
            $footerText = 'Respond with "' . $commandName . ' [ACTION]" to perform the corresponding action. ' . PHP_EOL
                . 'Available actions: ';
            foreach ($this->actions as $action) {
                $footerText .= mb_strtolower($action->name) . ', ';
            }
            $footerText .= DEFAULT_ACTION;
            $embedResponse->setFooter($footerText);
        }
        return $embedResponse;
    }

    /**
     * @param string $actionName
     * @return Action|bool Returns TRUE if DEFAULT_ACTION
     */
    public function getActionIfValid(string $actionName) {
        $actionL = mb_strtolower($actionName);
        if ($actionL === mb_strtolower(DEFAULT_ACTION)) {
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
}