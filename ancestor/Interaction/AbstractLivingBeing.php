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

    public function isStealthed(): bool {
        return $this->statManager->isStealthed();
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
        if ($target->statManager->isStealthed() && !$action->isUsableVsStealth()) {
            $action = $this->type->defaultAction();
        }
        $title = ('**' . $this->name . '** uses **' . $action->name . '**!');
        $effect = $action->effect;
        $hit = $this->getDAEffectResultFields($effect, $target, $title, $res);
        if ($target->isDead()) {
            return $res;
        }
        if ($target->type->riposteAction !== null && $target->statManager->has(StatusEffect::TYPE_RIPOSTE)
            && !($effect->isHealEffect() || $effect->isPositiveStressEffect())) {
            $riposteHit = $target->getDAEffectResultFields($target->type->riposteAction->effect, $this, '***' . $target->name . ' counter-attacks!***', $res);
            $target->applyTimedEffectsGetResults($target->type->riposteAction->statusEffects, $this, $res, $riposteHit);
            $target->applyTimedEffectsGetResults($target->type->riposteAction->statModifiers, $this, $res, $riposteHit);
        }

        $this->applyTimedEffectsGetResults($action->statusEffects, $target, $res, $hit);
        $this->applyTimedEffectsGetResults($action->statModifiers, $target, $res, $hit);

        if ($hit && $action->selfEffect !== null && !$this->isDead()) {
            $this->getDAEffectResultFields($action->selfEffect, $this, $action->name, $res);
        }

        return $res;
    }

    /**
     * @param DirectActionEffect $effect
     * @param AbstractLivingBeing $target
     * @param string $title
     * @param $res
     * @return bool True if HIT, false if MISS
     */
    protected function getDAEffectResultFields(DirectActionEffect $effect, AbstractLivingBeing $target, string $title, &$res): bool {
        list('hit' => $isHit, 'crit' => $isCrit, 'healthValue' => $healthValue, 'stressValue' => $stressValue)
            = $effect->getApplicationResult($this, $target);

        if (!$isHit) {
            $res[] = Helper::getEmbedField($title, self::MISS_MESSAGE);
            return false;
        }

        $critField = null;
        if ($isCrit) {
            $title .= self::CRIT_MESSAGE;
            $critField = $this->getCritStressResult($effect, $stressValue);
        }
        $res[] = Helper::getEmbedField($title, $effect->getDescription());

        $target->tryRemoveBlightBleedWithEffect($effect, $res);
        if ($target->isStealthed() && $effect->removesStealth) {
            $target->statManager->removeStatusEffectType(StatusEffect::TYPE_STEALTH);
            $res[] = Helper::getEmbedField($target->name . ' is exposed!', '``Removed`` **``stealth``**.');
        }

        if ($healthValue !== 0) {
            if ($effect->isDamageEffect() && $target->statManager->tryBlock()) {
                $res[] = Helper::getEmbedField('**' . $target->name . '** has **``blocked``** the damage!',
                    $target->statManager->getStatusEffectState(StatusEffect::TYPE_BLOCK) ?? $target->name . ' is out of blocks.');
                $healthValue = 0;
            }
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
        }

        return true;
    }

    /**
     * @param StatusEffect[]|StatModifier[]|null $timedEffects
     * @param AbstractLivingBeing $target
     * @param bool $hit
     * @param array $result
     */
    protected function applyTimedEffectsGetResults($timedEffects, AbstractLivingBeing $target, array &$result, bool $hit) {
        if ($timedEffects === null) {
            return;
        }
        foreach ($timedEffects as $timedEffect) {
            if (!$hit && !$timedEffect->targetsSelf()) {
                continue;
            }
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
        $toAdd->chance += $this->statManager->getStatValue($toAdd->getType() . Stats::SKILL_CHANCE_SUFFIX) ?? 0;
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
            . ($toAdd->getType() === StatusEffect::TYPE_STUN ? ' is stunned!' : ' now has **``' . $toAdd->getType() . '``**')
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

    /**
     * @return string[]
     */
    public function getAllTypes() {
        return array_merge($this->type->types, $this->statManager->getStatusesNames());
    }

    protected function tryRemoveBlightBleedWithEffect(DirectActionEffect $effect, array &$res) {
        $removedBleeds = $effect->removesBleed && $this->statManager->removeBleeds();
        $removedBlights = $effect->removesBlight && $this->statManager->removeBlight();
        if (!$removedBleeds && !$removedBlights) {
            return;
        }
        $title = $this->name . ' is cured!';
        if (!$removedBlights && $removedBleeds) {
            $res[] = Helper::getEmbedField($title, 'Removed bleeds.');
            return;
        }
        if ($removedBlights && !$removedBleeds) {
            $res[] = Helper::getEmbedField($title, 'Removed blights.');
            return;
        }
        $res[] = Helper::getEmbedField($title, 'Removed blights amd bleeds.');
    }

}