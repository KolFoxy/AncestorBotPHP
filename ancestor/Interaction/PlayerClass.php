<?php

namespace Ancestor\Interaction;

use CharlotteDunois\Yasmin\Models\Message;
use CharlotteDunois\Yasmin\Models\MessageEmbed;

class PlayerClass extends AbstractLivingInteraction {

    const EMBED_COLOR = 13294;
    /**
     * @var int
     */
    public $stress = 0;

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

    public function getEmbedResponse(string $commandName): MessageEmbed {
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
        $footerText = 'Health: *' . $this->getHealthStatus() . '* | Stress: *' . $this->getStressStatus() . '*';
        $embedResponse->setFooter($footerText);

        return $embedResponse;
    }

    public function getStressStatus(): string {
        return $this->stress . '/100';
    }

}