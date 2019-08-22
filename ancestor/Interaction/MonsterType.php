<?php

namespace Ancestor\Interaction;

use CharlotteDunois\Yasmin\Models\MessageEmbed;

class MonsterType extends AbstractLivingInteraction {

    /**
     * @var DirectAction|null
     */
    private $defAction = null;

    /**
     * @var \Ancestor\Interaction\Stats\StatusEffect[]|null
     */
    public $startingStatusEffects = null;

    /**
     * @var null|MonsterActionsManager
     */
    public $actionsManager = null;

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

    public function defaultAction(): DirectAction {
        if ($this->defAction === null) {
            $action = new DirectAction();
            $action->name = 'pass turn';
            $action->requiresTarget = true;
            $effect = new DirectActionEffect();
            $effect->setDescription($this->name.' passed the turn.');
            $effect->hitChance = -1;
            $effect->critChance = -1;
            $action->effect = $effect;
            $this->defAction = $action;
        }
        return $this->defAction;
    }

    /**
     * @param int|string $actionName
     * @return DirectAction
     */
    public function getActionIfValid($actionName): DirectAction {
        return parent::getActionIfValid($actionName);
    }
}