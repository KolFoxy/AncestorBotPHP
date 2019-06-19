<?php

namespace Ancestor\Interaction;

const DEFAULT_EMBED_COLOR = 13632027;

use CharlotteDunois\Yasmin\Models\MessageEmbed;

class Curio extends AbstractInteraction {

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
            $footerText .= self::DEFAULT_ACTION;
            $embedResponse->setFooter($footerText);
        }
        return $embedResponse;
    }
}