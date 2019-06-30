<?php


namespace Ancestor\Interaction;

use CharlotteDunois\Yasmin\Models\MessageEmbed;

class MonsterType extends AbstractLivingInteraction {

    /**
     * @var DirectAction|null
     */
    private $defAction = null;


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
     * @return DirectAction
     */
    public function getRandomAction(): DirectAction {
        return $this->actions[mt_rand(0, sizeof($this->actions) - 1)];
    }

    public function defaultAction(): DirectAction {
        if ($this->defAction === null) {
            $action = new DirectAction();
            $action->name = 'attack';
            $effect = new Effect();
            $effect->name = 'Attack!';
            $effect->setDescription('Monster attacks the hero!');
            $effect->health_value = (-1) * mt_rand(3, 10);
            $action->effect = $effect;
            $this->defAction = $action;
        }
        return $this->defAction;
    }

    public function getActionIfValid(string $actionName) : DirectAction {
        return parent::getActionIfValid($actionName);
    }
}