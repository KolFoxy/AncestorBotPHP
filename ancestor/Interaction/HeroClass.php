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
     * Path to the template.
     * @var string|null
     */
    public $imageTemplate = null;

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
        if ($this->imageTemplate === null) {
            $embedResponse->setThumbnail($this->image);
        }
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

}
