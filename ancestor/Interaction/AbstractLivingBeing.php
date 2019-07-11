<?php

namespace Ancestor\Interaction;

use Ancestor\Interaction\Stats\Stats;
use Ancestor\Interaction\Stats\StatsManager;
use Ancestor\Interaction\Stats\StatusEffect;
use Ancestor\Interaction\Stats\TimedEffectInterface;

abstract class AbstractLivingBeing {

    const CRIT_STRESS_HEAL = -3;


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
     * @param Effect $effect
     * @return bool
     */
    public function rollWillHit(AbstractLivingBeing $target, Effect $effect): bool {
        if ($effect->hitChance >= 0 && mt_rand(1, $this->statManager->getStatValue(Stats::ACC_MOD) + $effect->hitChance)
            <= $target->statManager->getStatValue(Stats::DODGE)) {
            return false;
        }
        return true;
    }

    public function rollWillCrit(Effect $effect): bool {
        return $effect->canCrit() && mt_rand(1, 100) <= ($effect->critChance + $this->statManager->getStatValue(Stats::CRIT_CHANCE));
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

        if (!$this->rollWillHit($target, $effect)) {
            $res[] = ['name' => $title, 'value' => self::MISS_MESSAGE, 'inline' => false];
            return $res;
        }

        $isCrit = $this->rollWillCrit($effect);
        $stressValue = (int)($effect->getStressValue() * $this->statManager->getValueMod(Stats::STRESS_SKILL_MOD));
        $healthValue = (int)($effect->getHealthValue());
        if ($isCrit) {
            $title .= self::CRIT_MESSAGE;
            $healthValue *= 2;
        }

        $res[] = [
            'name' => $title,
            'value' => $effect->getDescription(),
            'inline' => false,
        ];

        if ($healthValue !== 0) {
            $healthValue *= $healthValue < 0
                ? $this->statManager->getValueMod(Stats::DAMAGE_MOD) * $target->statManager->getValueMod(Stats::PROT)
                : $this->statManager->getValueMod(Stats::HEAL_SKILL_MOD) * $target->statManager->getValueMod(Stats::HEAL_RECEIVED_MOD);
            $target->addHealth($healthValue);
            $effectString = $effect->isHealEffect() ? '** is healed for **' : '** gets hit for **';
            $res[] = [
                'name' => '**' . $target->name . $effectString . abs($healthValue) . 'HP**!',
                'value' => '*``' . $target->getHealthStatus() . '``*',
                'inline' => false,
            ];
            if ($isCrit) {
                if ($effect->isHealEffect()) {
                    $stressValue -= 10;
                } elseif ($this->hasStress()) {
                    $this->addStress(self::CRIT_STRESS_HEAL);
                    $res[] = [
                        'name' => '**' . $this->name . '** feels confident! **' . self::CRIT_STRESS_HEAL . ' stress**!',
                        'value' => '*``' . $this->getStressStatus() . '``*',
                        'inline' => false,
                    ];
                }
            }
        }

        if ($stressValue !== 0 && $target->hasStress()) {
            $target->addStress($stressValue
                * $this->statManager->getValueMod($stressValue < 0 ? Stats::STRESS_HEAL_MOD : Stats::STRESS_MOD)
            );
            $effectString = '** suffers **';
            if ($stressValue < 0) {
                $effectString = '** feels less tense. **';
            }
            $res[] = [
                'name' => '**' . $target->name . $effectString . $stressValue . ' stress**!',
                'value' => '*``' . $target->getStressStatus() . '``*',
                'inline' => false,
            ];
        }

        if ($target->isDead()) {
            $res[] = [
                'name' => '***DEATHBLOW***',
                'value' => '***' . $target->getDeathQuote() . '***',
                'inline' => false,
            ];
            return $res;
        }
        if ($action->statusEffects !== null) {
            foreach ($action->statusEffects as $statusEffect) {
                $toAdd = $statusEffect->clone();
                $effectTarget = $toAdd->targetSelf ? $this : $target;
                if ($effectTarget->statManager->addStatusEffect($toAdd)) {
                    $nameString = $toAdd->getType() === StatusEffect::TYPE_STUN ? ' is stunned!' : ' now has **``' . $toAdd->getType() . '``**';
                    $res[] = [
                        'name' => $effectTarget->name . $nameString,
                        'value' => '``' . $effectTarget->statManager->getStatusEffectState($toAdd->getType()) . '``',
                        'inline' => true,
                    ];
                } else {
                    $res[] = $this->getFailedApplicationField($toAdd, $effectTarget);
                }
            }
        }
        if ($action->statModifiers !== null) {
            foreach ($action->statModifiers as $statModifier) {
                $toAdd = $statModifier->clone();
                $effectTarget = $toAdd->targetSelf ? $this : $target;
                if ($effectTarget->statManager->addModifier($toAdd)) {
                    $res[] = [
                        'name' => $effectTarget->name . ' now has a **``' . $toAdd->getType() . '``**',
                        'value' => '``Current``**`` ' . $toAdd->getStat() . '``**``: '
                            . $effectTarget->statManager->getStatValue($toAdd->getStat()) . '``',
                        'inline' => true,
                    ];
                } else {
                    $res[] = $this->getFailedApplicationField($toAdd, $effectTarget);
                }
            }
        }
        return $res;
    }

    protected function getFailedApplicationField(TimedEffectInterface $timedEffect, AbstractLivingBeing $effectTarget): array {
        return [
            'name' => $effectTarget->name . ' has resisted **``' . $timedEffect->getType() . '``**',
            'value' => '**``' . $timedEffect->getType() . '``**`` resist: '
                . $effectTarget->statManager->getStatValue($timedEffect->getType() . Stats::RESIST_SUFFIX) . '%``',
            'inline' => true,
        ];
    }


}