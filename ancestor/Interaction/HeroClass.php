<?php

namespace Ancestor\Interaction;

use CharlotteDunois\Yasmin\Models\Message;
use CharlotteDunois\Yasmin\Models\MessageEmbed;

class HeroClass extends AbstractLivingInteraction {

    /**
     * @var HeroAction|null
     */
    private $defAction = null;

    const EMBED_COLOR = 13294;

    /**
     * Color of the embedResponse
     * @var null|integer
     */
    public $embedColor = null;

    /**
     * @var HeroAction
     */
    public $actions;

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

    /**
     * @return Action|HeroAction
     */
    public function defaultAction(): Action {
        if ($this->defAction === null) {
            $action = new HeroAction();
            $action->name = 'pass turn';
            $effect = new Effect();
            $effect->name = 'Do nothing.';
            $effect->setDescription('Hero passed the turn and suffered stress.');
            $effect->stress_value = mt_rand(6, 10);
            $action->effects = [$effect];
            $this->defAction = $action;
        }
        return $this->defAction;
    }

    /**
     * @param string $actionName
     * @return HeroAction
     */
    public function getActionIfValid(string $actionName) : HeroAction {
        if ($actionName === $this->defaultAction()->name) {
            return $this->defaultAction();
        }
        foreach ($this->actions as $action) {
            if (mb_strpos($action->name, $actionName) === 0) {
                return $action;
            }
        }
        return null;
    }
}
