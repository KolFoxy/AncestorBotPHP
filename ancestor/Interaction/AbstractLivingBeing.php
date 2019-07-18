<?php

namespace Ancestor\Interaction;

use Ancestor\Interaction\Stats\StatModifier;
use Ancestor\Interaction\Stats\Stats;
use Ancestor\Interaction\Stats\StatsManager;
use Ancestor\Interaction\Stats\StatusEffect;
use Ancestor\Interaction\Stats\TimedEffectInterface;
use CharlotteDunois\Yasmin\Models\MessageEmbed;
use Ancestor\CommandHandler\CommandHelper as Helper;

abstract class AbstractLivingBeing {

    const CRIT_STRESS_SELF_HEAL = -3;
    const CRIT_STRESS = 10;
    const CRIT_HEAL_STRESS_RELIEF = -4;


    const MISS_MESSAGE = '``...and misses!``';
    const CRIT_MESSAGE = ' ***CRIT!***';

    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $healthMax;

    /**
     * @var int
     */
    protected $currentHealth;

    /**
     * @var StatsManager;
     */
    public $statManager;

    /**
     * @var AbstractLivingInteraction;
     */
    public $type;

    /**
     * @var int|null
     */
    public $stress = null;


    /**
     * @return string Format: "Health: currentHealth/healthMax"
     */

    public function getHealthStatus(): string {
        return 'Health: ' . $this->currentHealth . '/' . $this->healthMax;
    }

    public function addStress(int $value) {
        if ($this->hasStress()) {
            $this->stress += $value;
        }
    }

    public function getStressStatus(): string {
        if (!$this->hasStress()) {
            return '';
        }
        return 'Stress: ' . $this->stress;
    }

    public function hasStress(): bool {
        return $this->stress !== null;
    }

    /**
     * @return bool
     */
    public function isDead(): bool {
        return $this->currentHealth <= 0;
    }

    /**
     * @param AbstractLivingBeing $target
     * @param DirectActionEffect $effect
     * @return bool
     */
    public function rollWillHit(AbstractLivingBeing $target, DirectActionEffect $effect): bool {
        if ($effect->hitChance >= 0 && mt_rand(1, $this->statManager->getStatValue(Stats::ACC_MOD) + $effect->hitChance)
            <= $target->statManager->getStatValue(Stats::DODGE)) {
            return false;
        }
        return true;
    }

    public function rollWillCrit(DirectActionEffect $effect): bool {
        return $effect->canCrit() && mt_rand(1, 100) <=
            ($effect->critChance + $this->statManager->getStatValue(Stats::CRIT_CHANCE));
    }

    public function __construct(AbstractLivingInteraction $type) {
        $this->name = $type->name;
        $this->type = $type;
        $this->currentHealth = $this->healthMax = $type->healthMax;
        $this->statManager = new StatsManager($this, $type->stats);
    }

    /**
     * @param int $value
     */
    abstract public function addHealth(int $value);

    abstract public function getDeathQuote(): string;

    public function getStunnedTurn(): array {
        $res = $this->statManager->getProcessTurn();
        if ($res === null) {
            $res = [];
        }
        array_push($res,
            [
                'name' => '**' . $this->name . '** was stunned!',
                'value' => '...and did nothing.',
                'inline' => false,
            ]
        );

        if ($this->isDead()) {
            $res[] = $this->getDeathFromDotField();
        }
        return $res;
    }

    private function getDeathFromDotField(): array {
        return [
            'name' => '***' . $this->name . ' has deceased.***',
            'value' => '***' . $this->getDeathQuote() . '***',
            'inline' => false,
        ];
    }

    /**
     * @param AbstractLivingBeing $target
     * @param DirectAction $action
     * @return array Array of fields representing results of the turn Format: [['name' => string, 'value' => string, 'inline' => bool]]
     */
    public function getTurn(AbstractLivingBeing $target, DirectAction $action): array {
        if ($this->statManager->isStunned()) {
            return $this->getStunnedTurn();
        }
        $res = $this->statManager->getProcessTurn();
        if ($res === null) {
            $res = [];
        }
        if ($this->isDead()) {
            $res[] = $this->getDeathFromDotField();
            return $res;
        }

        $title = ('**' . $this->name . '** uses **' . $action->name . '**!');
        $effect = $action->effect;
        list('hit' => $isHit, 'crit' => $isCrit, 'healthValue' => $healthValue, 'stressValue' => $stressValue)
            = $effect->getApplicationResult($this, $target);

        if (!$isHit) {
            $res[] = Helper::getEmbedField($title, self::MISS_MESSAGE);
            return $res;
        }
        $critField = null;
        if ($isCrit) {
            $title .= self::CRIT_MESSAGE;
            $critField = $this->getCritStressResult($effect, $stressValue);
        }
        $res[] = Helper::getEmbedField($title, $effect->getDescription());
        if ($healthValue !== 0) {
            $target->addHealth($healthValue);
            $res[] = Helper::getEmbedField(
                '**' . $target->name . ($effect->isHealEffect() ? '** is healed for **' : '** gets hit for **') . abs($healthValue) . 'HP**!',
                '*``' . $target->getHealthStatus() . '``*');
        }
        if ($critField !== null) {
            $res[] = $critField;
        }
        if ($stressValue !== 0 && $target->hasStress()) {
            $target->addStress($stressValue);
            $res[] = Helper::getEmbedField(
                '**' . $target->name . ($stressValue > 0 ? '** suffers **' : '** feels less tense. **') . $stressValue . ' stress**!',
                '*``' . $target->getStressStatus() . '``*');
        }
        if ($target->isDead()) {
            $res[] = Helper::getEmbedField('***DEATHBLOW***', '***' . $target->getDeathQuote() . '***');
            return $res;
        }

        $this->applyEffectsGetResults($action->statusEffects, $target, $res);
        $this->applyEffectsGetResults($action->statModifiers, $target, $res);

        return $res;
    }

    /**
     * @param StatusEffect[]|StatModifier[]|null $timedEffects
     * @param AbstractLivingBeing $target
     * @param array $result
     */
    protected function applyEffectsGetResults($timedEffects, AbstractLivingBeing $target, array &$result) {
        if ($timedEffects === null) {
            return;
        }
        foreach ($timedEffects as $timedEffect) {
            $result[] = $this->getEffectApplicationField($timedEffect, $target);
        }
    }

    /**
     * @param StatusEffect|StatModifier $timedEffect
     * @param AbstractLivingBeing $target
     * @return array
     */
    protected function getEffectApplicationField($timedEffect, AbstractLivingBeing $target): array {
        $toAdd = $timedEffect->clone();
        $effectTarget = $toAdd->targetSelf ? $this : $target;
        $isSE = is_a($toAdd, StatusEffect::class);
        if (!($isSE ? $effectTarget->statManager->addStatusEffect($toAdd) : $effectTarget->statManager->addModifier($toAdd))) {
            return $this->getFailedApplicationField($toAdd, $effectTarget);
        }

        $value = $isSE ? '``' . $effectTarget->statManager->getStatusEffectState($toAdd->getType()) . '``' :
            '``' . $toAdd->__toString() . '``' . PHP_EOL
            . '``Current``**`` ' . Stats::formatName($toAdd->getStat()) . '``**``: '
            . $effectTarget->statManager->getStatValue($toAdd->getStat()) . '``';
        return Helper::getEmbedField($effectTarget->name
        . $toAdd->getType() === StatusEffect::TYPE_STUN ? ' is stunned!' : ' now has **``' . $toAdd->getType() . '``**'
            , $value);
    }

    protected function getFailedApplicationField(TimedEffectInterface $timedEffect, AbstractLivingBeing $effectTarget): array {
        return [
            'name' => $effectTarget->name . ' has resisted **``' . $timedEffect->getType() . '``**',
            'value' => '**``' . $timedEffect->getType() . '``**`` resist: '
                . $effectTarget->statManager->getStatValue($timedEffect->getType() . Stats::RESIST_SUFFIX) . '%``',
            'inline' => true,
        ];
    }

    /**
     * @param $criticalEffect
     * @param int $stressValue
     * @return array|null
     */
    protected function getCritStressResult(DirectActionEffect $criticalEffect, int &$stressValue) {
        if ($criticalEffect->isHealEffect()) {
            $stressValue += self::CRIT_HEAL_STRESS_RELIEF;
            return null;
        }
        $stressValue += self::CRIT_STRESS;
        if (!$this->hasStress()) {
            return null;
        }
        $this->addStress(self::CRIT_STRESS_SELF_HEAL);
        return Helper::getEmbedField('**' . $this->name . '** feels confident! **' . self::CRIT_STRESS_SELF_HEAL . ' stress**!',
            '*``' . $this->getStressStatus() . '``*');

    }

    public function getStatsAndEffectsEmbed(): MessageEmbed {
        $res = new MessageEmbed();
        $res->setTitle('**' . $this->name . '**');
        $res->setDescription('*``' . $this->type->description . '``*');
        $res->addField('**Stats:**', $this->statManager->getCurrentStatsString(), true);
        $res->setThumbnail($this->type->image);
        $effects = $this->statManager->getAllCurrentEffectsString();
        if ($effects !== '') {
            $res->addField('**Effects:**', $effects, true);
        }
        return $res;
    }

    /**
     * @return string[]
     */
    public function getAllTypes() {
        return array_merge($this->type->types, $this->statManager->getStatusesNames());
    }


}