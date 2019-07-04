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

    /**
     * @param Hero $heroTarget
     * @param DirectAction|null $forcedAction
     * @return array Array of embed fields [ 'name' => string, 'value' => string, 'inline' => bool ]
     */
    function getMonsterTurn(Hero $heroTarget, DirectAction $forcedAction = null): array {
        $res = [];
        $action = is_null($forcedAction) ? $this->type->getRandomAction() : $forcedAction;
        $effect = $action->effect;
        $title = ('**' . $this->type->name . '** uses **' . $action->name . '**!');
        if (!$this->rollWillHit($heroTarget, $effect)) {
            $res[] = ['name' => $title, 'value' => self::MISS_MESSAGE, 'inline' => false];
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

        $res[] = ['name' => $title, 'value' => '*``' . $effect->getDescription() . '``*', 'inline' => false];

        $heroTarget->addHealth($healthEffect);
        if ($healthEffect !== 0) {
            $res[] = [
                'name' => '**' . $heroTarget->name . '** gets hit for **' . abs($healthEffect) . 'HP**!',
                'value' => '*``' . $heroTarget->getHealthStatus() . '``*',
                'inline' => false,
            ];
        }
        $heroTarget->addStress($stressEffect);
        if ($stressEffect !== 0) {
            $res[] = [
                'name' => '**' . $heroTarget->name . '** suffers **' . $stressEffect . ' stress**!',
                'value' => '*``' . $heroTarget->getStressStatus() . '``*',
                'inline' => false,
            ];
        }

        if ($heroTarget->isDead()) {
            $res[] = [
                'name' => '***DEATHBLOW***',
                'value' => '***' . RandomDataProvider::GetInstance()->GetRandomHeroDeathQuote() . '***',
                'inline' => false,
            ];
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