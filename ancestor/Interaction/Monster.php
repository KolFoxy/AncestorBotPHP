<?php

namespace Ancestor\Interaction;

use Ancestor\RandomData\RandomDataProvider;
use CharlotteDunois\Yasmin\Models\MessageEmbed;

class Monster extends AbstractLivingBeing {

    /**
     * @var MonsterType
     */
    public $type;

    public function __construct(MonsterType $monsterType) {
        parent::__construct($monsterType);
    }

    /**
     * @param string $commandName
     * @param Action[]|null $userActions
     * @return MessageEmbed
     */
    public function getEmbedResponse(string $commandName, array $userActions = null): MessageEmbed {
        return $this->type->getEmbedResponse($commandName, $userActions, $this->getHealthStatus());
    }

//TODO: Change return value to Field Array
    function getMonsterTurn(Hero $heroTarget, DirectAction $forcedAction = null): MessageEmbed {
        $res = new MessageEmbed();
        $action = is_null($forcedAction) ? $this->type->getRandomAction() : $forcedAction;
        $effect = $action->effect;
        $title = ('**' . $this->type->name . '** uses **' . $action->name . '**!');
        if (!$this->rollWillHit($heroTarget, $effect)) {
            $res->setDescription(self::MISS_MESSAGE);
            $res->setTitle($title);
            return $res;
        }

        $isCrit = $this->rollWillCrit($effect);
        $stressEffect = $effect->getStressValue();
        $healthEffect = $effect->getHealthValue();
        if ($isCrit) {
            $title .= self::CRIT_MESSAGE;
            $stressEffect += 10;
            $healthEffect *= 2;
        }

        $res->setTitle($title);
        $res->setDescription('*``' . $effect->getDescription() . '``*');

        $heroTarget->addStressAndHealth($stressEffect, $healthEffect);

        if ($stressEffect !== 0) {
            $res->addField('**' . $heroTarget->name . '** suffers **' . $stressEffect . ' stress**!'
                , '*``' . $heroTarget->getStressStatus() . '``*');
        }

        if ($healthEffect !== 0) {
            $res->addField('**' . $heroTarget->name . '** gets hit for **' . abs($healthEffect) . 'HP**!'
                , '*``' . $heroTarget->getHealthStatus() . '``*');
        }

        if ($heroTarget->isDead()) {
            $res->addField('***DEATHBLOW***', '***' . RandomDataProvider::GetInstance()->GetRandomHeroDeathQuote() . '***');
        }
        return $res;
    }


    public function addHealth(int $value) {
        $this->currentHealth += $value;
        if ($this->currentHealth > $this->healthMax) {
            $this->currentHealth = $this->healthMax;
        }
    }

}