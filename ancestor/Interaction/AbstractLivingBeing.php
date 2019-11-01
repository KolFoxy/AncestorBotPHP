<?php

namespace Ancestor\Interaction;

use Ancestor\Interaction\ActionResult\ActionResult;
use Ancestor\Interaction\Stats\StatModifier;
use Ancestor\Interaction\Stats\Stats;
use Ancestor\Interaction\Stats\StatsManager;
use Ancestor\Interaction\Stats\StatusEffect;

abstract class AbstractLivingBeing {

    const DEFAULT_STRESS_SELF_HEAL = -3;
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
     * @var string|null
     */
    public $causeOfDeath = null;


    /**
     * @return string Format: "Health: currentHealth/healthMax"
     */

    const KILLER_CAUSE_OF_DEATH = 'Killed by a deadly ';

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

    /**
     * @param int $value
     * @return int For how much the being was healed
     */
    public function heal(int $value): int {
        $value = (int)($this->statManager->getValueMod(Stats::HEAL_RECEIVED_MOD) * $value);
        $this->addHealth($value);
        return $value;
    }

    abstract public function getDeathQuote(): string;

    public function getStunnedTurn(): array {
        $res = $this->statManager->getProcessTurn();
        if ($res === null) {
            $res = [];
        }
        array_unshift($res,
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
     * @param DirectAction|null $action
     * @return array Array of fields representing results of the turn Format: [['name' => string, 'value' => string, 'inline' => bool]]
     */
    public function getTurn(AbstractLivingBeing $target, ?DirectAction $action = null): array {
        if ($this->statManager->isStunned()) {
            return $this->getStunnedTurn();
        }
        if ($action === null) {
            $action = $this->type->getRandomAction();
        }
        $turnFields = $this->statManager->getProcessTurn();
        if ($turnFields === null) {
            $turnFields = [];
        }
        if ($this->isDead()) {
            $turnFields[] = $this->getDeathFromDotField();
            return $turnFields;
        }
        if ($target->statManager->isStealthed() && !$action->isUsableVsStealth()) {
            $action = $this->type->defaultAction();
            $target = $this;
        }
        $effect = $action->effect;
        $actRes = new ActionResult($this, $target, $action->name, $effect->getDescription());
        $hit = $this->getDAEffectResult($effect, $target, $actRes);
        $riposteRes = null;
        if (!$target->isDead()) {
            if ($target->type->riposteAction !== null && $target->statManager->has(StatusEffect::TYPE_RIPOSTE)
                && ($effect->isDamageEffect() || $effect->isNegativeStressEffect())) {
                $riposteRes = new ActionResult($target, $this, $target->type->riposteAction->name, $target->type->riposteAction->effect->getDescription());
                $riposteHit = $target->getDAEffectResult($target->type->riposteAction->effect, $this, $riposteRes);
                $target->applyTimedEffectsGetResults($target->type->riposteAction->statusEffects, $riposteRes, $riposteHit);
                $target->applyTimedEffectsGetResults($target->type->riposteAction->statModifiers, $riposteRes, $riposteHit);
                if ($this->isDead()) {
                    $this->causeOfDeath = $target->killedByMessage();
                }
            }
        } elseif ($target->causeOfDeath === null) {
            $target->causeOfDeath = $this->killedByMessage();
        }
        $this->applyTimedEffectsGetResults($action->statusEffects, $actRes, $hit);
        $this->applyTimedEffectsGetResults($action->statModifiers, $actRes, $hit);
        $extra = $riposteRes === null ? '' : '**' . $target->name . ' counter-attacks!**' . PHP_EOL . $riposteRes->__toString();
        if (!$this->isDead() && $action->selfEffect !== null && ($action->selfEffect->removesStealth || $hit)) {
            $selfEffectRes = new ActionResult($this, $this, $action->selfEffect->getDescription(), $action->selfEffect->getDescription());
            $this->getDAEffectResult($action->selfEffect, $this, $selfEffectRes);
            if ($riposteRes !== null) {
                $extra .= PHP_EOL;
            }
            $extra .= $selfEffectRes->__toString();
        }
        $turnFields[] = $actRes->toFields($extra);
        return $turnFields;
    }

    /**
     * @param DirectActionEffect $effect
     * @param AbstractLivingBeing $target
     * @param ActionResult $actRes
     * @return bool True if HIT, false if MISS
     */
    protected function getDAEffectResult(DirectActionEffect $effect, AbstractLivingBeing $target, ActionResult $actRes): bool {
        list('hit' => $isHit, 'crit' => $isCrit, 'healthValue' => $healthValue, 'stressValue' => $stressValue)
            = $effect->getApplicationResult($this, $target);

        if (!$isHit) {
            $actRes->setMiss();
            return false;
        }

        if ($isCrit) {
            $actRes->setCrit();
            $this->getCritStressResult($effect, $actRes);
        }

        $actRes->removeBlightBleedStealthFromTarget($effect->removesBlight, $effect->removesBleed, $effect->removesStealth);
        $this->dAEffectAddHealth($effect, $healthValue, $target, $actRes);

        if (!$target->isDead()) {
            $actRes->stressToTarget($stressValue);
        }

        return true;

    }

    /**
     * @param DirectActionEffect $effect
     * @param int $healthValue
     * @param AbstractLivingBeing $target
     * @param ActionResult $actRes
     * @return AbstractLivingBeing Returns target
     */
    protected function dAEffectAddHealth(DirectActionEffect $effect, int $healthValue, AbstractLivingBeing $target, ActionResult $actRes): AbstractLivingBeing {
        $source = '';
        if ($healthValue === 0) {
            if ($effect->isDamageEffect() || $effect->isHealEffect()) {
                $source = 'Oof';
            }
        } elseif ($effect->isDamageEffect() && $target->statManager->tryBlock()) {
            $source = 'Block';
            $healthValue = 0;
            if ($target->statManager->getStatusEffectState(StatusEffect::TYPE_BLOCK) === null) {
                $actRes->removedFromTarget(StatusEffect::TYPE_BLOCK);
            }
        }
        $actRes->healthToTarget($healthValue, $source);
        return $target;
    }

    /**
     * @param StatusEffect[]|StatModifier[]|null $timedEffects
     * @param bool $hit
     * @param ActionResult $result
     */
    protected function applyTimedEffectsGetResults($timedEffects, ActionResult $result, bool $hit) {
        if ($timedEffects === null) {
            return;
        }
        foreach ($timedEffects as $timedEffect) {
            if (!$hit && !$timedEffect->targetsSelf()) {
                continue;
            }
            $result->addTimedEffect($timedEffect);
        }
    }

    /**
     * @param $criticalEffect
     * @param ActionResult $actRes
     */
    protected function getCritStressResult(DirectActionEffect $criticalEffect, ActionResult $actRes) {
        if ($criticalEffect->isHealEffect()) {
            $actRes->stressToTarget(self::CRIT_HEAL_STRESS_RELIEF, 'Crit!');
            return;
        }
        $actRes->stressToTarget(self::CRIT_STRESS, 'Crit!');
        if (!$this->hasStress()) {
            return;
        }
        $actRes->stressToCaster(self::DEFAULT_STRESS_SELF_HEAL, 'Crit!');
    }

    /**
     * @return string[]
     */
    public function getAllTypes() {
        return array_merge($this->type->types, $this->statManager->getStatusesNames());
    }

    public function isStunned(): bool {
        return $this->statManager->isStunned();
    }

    public function killedByMessage(): string {
        return self::KILLER_CAUSE_OF_DEATH . $this->type->name;
    }


}