<?php

namespace Ancestor\Interaction;

use CharlotteDunois\Yasmin\Models\Message;
use CharlotteDunois\Yasmin\Models\MessageEmbed;

class HeroClass extends AbstractLivingInteraction {

    /**
     * @var DirectAction|null
     */
    private $defAction = null;

    const EMBED_COLOR = 13294;

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

    /**
     * @return DirectAction
     */
    public function defaultAction(): DirectAction {
        if ($this->defAction === null) {
            $action = new DirectAction();
            $action->name = 'pass turn';
            $action->requiresTarget = true;
            $effect = new Effect();
            $effect->name = 'Do nothing.';
            $effect->setDescription('Hero passed the turn and suffered stress.');
            $effect->stress_value = 6;
            $effect->stressDeviation = 4;
            $action->effect = $effect;
            $this->defAction = $action;
        }
        return $this->defAction;
    }

    /**
     * @param string $actionName
     * @return Action|DirectAction|null
     */
    public function getActionIfValid(string $actionName) {
        $actionL = mb_strtolower($actionName);
        if ($actionL === mb_strtolower($this->defaultAction()->name)) {
            return $this->defaultAction();
        }
        foreach ($this->actions as $action) {
            if (mb_strpos($actionL, mb_strtolower($action->name)) === 0) {
                return $action;
            }
        }
        return null;
    }
}
