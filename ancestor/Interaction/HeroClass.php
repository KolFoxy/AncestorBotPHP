<?php

namespace Ancestor\Interaction;

use CharlotteDunois\Yasmin\Models\Message;
use CharlotteDunois\Yasmin\Models\MessageEmbed;

class HeroClass extends AbstractInteraction {

    const EMBED_COLOR = 13294;

    /**
     * @var int
     * @required
     */
    public $healthMax;

    /**
     * Color of the embedResponse
     * @var null|integer
     */
    public $embedColor = null;

    /**
     * @param string $commandName
     * @param string|null $status
     * @return MessageEmbed
     */
    public function getEmbedResponse(string $commandName = null, string $status = null): MessageEmbed {
        $embedResponse = new MessageEmbed();
        $embedResponse->setThumbnail($this->image);
        $embedResponse->setTitle('Your class is **' . $this->name . '**');
        if ($this->embedColor === null) {
            $embedResponse->setColor(self::EMBED_COLOR);
        } else {
            $embedResponse->setColor($this->embedColor);
        }
        $embedResponse->setDescription('*' . $this->description . '*');
        if ($status != null) {
            $footerText = $status;
            $embedResponse->setFooter($footerText);
        }
        return $embedResponse;
    }

    public static function defaultAction(): Action {
        $action = new Action();
        $action->name = 'pass turn';
        $effect = new Effect();
        $effect->name = 'Do nothing.';
        $effect->setDescription('Hero passed the turn and suffered stress.');
        $effect->stress_value = mt_rand(6, 10);
        $action->effects = [$effect];
        return $action;
    }
}
