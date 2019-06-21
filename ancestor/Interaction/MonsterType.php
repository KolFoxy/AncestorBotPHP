<?php
/**
 * Created by PhpStorm.
 * User: KolBrony
 * Date: 19.06.2019
 * Time: 14:41
 */

namespace Ancestor\Interaction;

use CharlotteDunois\Yasmin\Models\MessageEmbed;

class MonsterType extends AbstractLivingInteraction {

    /**
     * @param string $commandName
     * @param HeroClass|null $attacker
     * @param string $healthStatus
     * @return MessageEmbed
     */
    public function getEmbedResponse(string $commandName, HeroClass $attacker = null, string $healthStatus = ''): MessageEmbed {
        $embedResponse = new MessageEmbed();
        $embedResponse->setThumbnail($this->image);
        if ($healthStatus != '') {
            $healthStatus = ' Health: **' . $healthStatus . '**';
        }
        $embedResponse->setTitle('**You encounter ' . $this->name
            . '.**' . $healthStatus);
        $embedResponse->setColor(DEFAULT_EMBED_COLOR);
        $embedResponse->setDescription('*' . $this->description . '*');
        if ($attacker != null) {
            $embedResponse->setFooter($attacker->getDefaultFooterText($commandName));
        }
        return $embedResponse;
    }

    /**
     * @return Action
     */
    public function getRandomAction(): Action {
        return $this->actions[mt_rand(0, sizeof($this->actions))];
    }

    public static function defaultAction(): Action {
        $action = new Action();
        $action->name = 'attack';
        $effect = new Effect();
        $effect->name = 'Attack!';
        $effect->setDescription('Monster attacks the hero!');
        $effect->health_value = (-1) * mt_rand(3, 10);
        $action->effects = [$effect];
        return $action;
    }
}