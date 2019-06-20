<?php

namespace Ancestor\Interaction;

const DEFAULT_EMBED_COLOR = 13632027;

use CharlotteDunois\Yasmin\Models\MessageEmbed;

class Curio extends AbstractInteraction {

    private static $defAction = null;

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

    public static function defaultAction(): Action {
        if (self::$defAction === null) {
            $action = new Action();
            $action->name = 'nothing';
            $effect = new Effect();
            $effect->name = 'Nothing happened.';
            $effect->setDescription('You choose to walk away in peace.');
            $action->effects = [$effect];
            self::$defAction = $action;
        }
        return self::$defAction;
    }

}