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

    function getMonsterTurn(Hero $heroTarget, DirectAction $forcedAction = null): MessageEmbed {
        $res = new MessageEmbed();
        $action = is_null($forcedAction) ? $this->type->getRandomAction() : $forcedAction;
        $res->setTitle('***' . $this->type->name . ' uses ' . $action->name . '!***');
        $res->setThumbnail($this->type->image);
        $res->setFooter($this->type->name . '\'s health: ' . $this->getHealthStatus());
        $effect = $action->effect;
        if (!$this->rollWillHit($heroTarget,$effect)) {
            $res->setDescription('...and misses!');
            return $res;
        }

        $description = $effect->getDescription();
        $isCrit = $this->rollWillCrit($effect);
        $stressEffect = $effect->getStressValue();
        $healthEffect = $effect->getHealthValue();
        if ($isCrit) {
            $description .= ' ***CRIT***';
            $stressEffect += 10;
        }
        $res->setDescription($description);
        $heroTarget->addStressAndHealth($stressEffect, $healthEffect);

        if ($stressEffect > 0) {
            $res->addField('``' . $heroTarget->name . ' suffers ' . $stressEffect . ' stress!``'
                , '*Stress: ' . $heroTarget->getStressStatus() . '*');
        }

        if ($healthEffect > 0) {
            $res->addField('``' . $heroTarget->name . ' gets hit for ' . $healthEffect . ' HP!``'
                , '*Health: ' . $heroTarget->getHealthStatus() . '*');
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