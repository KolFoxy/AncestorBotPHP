<?php

namespace Ancestor\Interaction;

const DEFAULT_EMBED_COLOR = 13632027;

use CharlotteDunois\Yasmin\Models\MessageEmbed;

class Curio extends AbstractInteraction {

    /**
     * @var Action|null
     */
    private $defAction = null;

    /**
     * @var Action[]
     */
    public $actions;


    /** @noinspection PhpDocMissingThrowsInspection */
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
        $embedResponse->setFooter($this->getDefaultFooterText($commandName));
        return $embedResponse;
    }

    public function defaultAction(): Action {
        if ($this->defAction === null) {
            $action = new Action();
            $action->name = 'nothing';
            $effect = new Effect();
            $effect->name = 'Nothing happened.';
            $effect->setDescription('You choose to walk away in peace.');
            $action->effects = [$effect];
            $this->defAction = $action;
        }
        return $this->defAction;
    }

    public function getActionIfValid(string $actionName): ?Action {
        return parent::getActionIfValid($actionName);
    }
}