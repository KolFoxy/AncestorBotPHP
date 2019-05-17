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
    public function getEmbedResponse(string $commandName) {
        $embedResponse = new MessageEmbed();
        $embedResponse->setThumbnail($this->image);
        $embedResponse->setTitle('**You encounter ' . $this->name . '**');
        $embedResponse->setColor(DEFAULT_EMBED_COLOR);
        $embedResponse->setDescription('*' . $this->description . '*');
        if (!empty($this->actions) || $this->actions instanceof Action) {
            $footerText = 'Respond with "' . $commandName . ' [ACTION]" to perform the corresponding action. ' . PHP_EOL
                . 'Available actions: ';
            foreach ($this->actions as $action) {
                $footerText .= $action->name . ', ';
            }
            $footerText .= DEFAULT_ACTION;
            $embedResponse->setFooter($footerText);
        }
        return $embedResponse;
    }
}