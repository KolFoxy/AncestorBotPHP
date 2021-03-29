<?php

namespace Ancestor\Interaction;

use Ancestor\Interaction\Stats\Stats;
use Ancestor\Interaction\Stats\TypeBonus;

class DirectActionEffect extends AbstractEffect {

    const DEFAULT_ACC_BONUS = 5;
    const FINALE = 'Finale.';

    /**
     * Chance of crit for the effect. negative - can't crit.
     * @var int
     */
    public int $critChance = 0;

    /**
     * @var int Negative for guarantee hit
     */
    public int $hitChance = 100;

    /**
     * @var TypeBonus[]|null
     */
    public ?array $typeBonuses = null;

    /**
     * @var bool
     */
    public bool $ignoresArmor = false;

    /**
     * @var bool
     */
    public bool $removesStealth = false;

    public function canCrit(): bool {
        return $this->critChance >= 0;
    }

    /**
     * @param AbstractLivingBeing $caster Who is trying to apply the effect on $target
     * @param AbstractLivingBeing $target The object of the effect.
     * @return array [ 'hit' => bool, 'crit' => bool, 'healthValue' => int, 'stressValue' => int ]
     */
    public function getApplicationResult(AbstractLivingBeing $caster, AbstractLivingBeing $target): array {
        $res = [
            'hit' => false,
            'crit' => false,
            'healthValue' => 0,
            'stressValue' => 0,
        ];
        $typeBonus = $this->getTotalTypeBonus($caster, $target);

        if (($res['hit'] = $this->rollWillHit($caster, $target, $typeBonus->accMod)) === false) {
            return $res;
        }
        $res['crit'] = $this->rollWillCrit($caster, $target, $typeBonus->critChanceMod);

        $critValueMod = $res['crit'] ? ($this->isHealEffect() ? 2.00 : 1.5) : 1.00;
        $value = $this->getHealthValue() * $critValueMod;
        if ($this->isHealEffect()) {
            $res['healthValue'] = (int)($value * ((100.00 + $caster->statManager->getHealModifier($target)) / 100.00));
        } elseif ($this->isDamageEffect()) {
            $res['healthValue'] = (int)($value * $this->getDamageModifier($caster, $target, $typeBonus->damageMod));
        }

        if ($this->isNegativeStressEffect()) {
            $res['stressValue'] = (int)($this->getStressValue() * $target->statManager->getValueMod(Stats::STRESS_MOD));
        } elseif ($this->isPositiveStressEffect()) {
            $res['stressValue'] = (int)($this->getStressValue() * (1.00 + $caster->statManager->getStressHealModifier($target) / 100.00));
        }

        return $res;
    }

    protected function getDamageModifier(AbstractLivingBeing $caster, AbstractLivingBeing $target, int $modifier): float {
        $modifier += $caster->statManager->getStatValue(Stats::DAMAGE_MOD)
            + $target->statManager->getStatValue(Stats::DAMAGE_TAKEN_MOD);
        if ($this->isFinale()) {
            $modifier += $caster->statManager->getStatValue(Stats::FINALE_DMG_MOD);
        }
        $protMod = $this->ignoresArmor ? 1.00 : $target->statManager->getValueMod(Stats::PROT);
        return (1.00 + $modifier / 100.00) * $protMod;
    }

    protected function isFinale(): bool {
        return mb_strpos($this->getDescription(), self::FINALE) !== false;
    }

    public function getTotalTypeBonus(AbstractLivingBeing $caster, AbstractLivingBeing $target): TypeBonus {
        $res = $caster->statManager->getBonusesVsTarget($target);
        $targetTypes = $target->getAllTypes();
        if ($this->typeBonuses === null) {
            return $res;
        }
        foreach ($this->typeBonuses as $bonus) {
            if (in_array($bonus->type, $targetTypes)) {
                $res->combineWith($bonus);
            }
        }
        return $res;
    }

    protected function rollWillCrit(AbstractLivingBeing $caster, AbstractLivingBeing $target, int $modifier): bool {
        return $this->canCrit() && mt_rand(1, 100) <=
            ($this->critChance + $modifier + $caster->statManager->getStatValue(Stats::CRIT_CHANCE)
                + $target->statManager->getStatValue(Stats::CRIT_RECEIVED_CHANCE));
    }

    protected function rollWillHit(AbstractLivingBeing $caster, AbstractLivingBeing $target, int $modifier): bool {
        if ($this->isHealEffect() || $this->isPositiveStressEffect() || $this->hitChance < 0) {
            return true;
        }
        $accuracy = $this->hitChance + $caster->statManager->getStatValue(Stats::ACC_MOD)
            + $modifier - $target->statManager->getStatValue(Stats::DODGE);
        if (mt_rand(1, 100) <= $accuracy || (mt_rand(1, 100) <= self::DEFAULT_ACC_BONUS)) {
            return true;
        }
        return false;
    }

    public function __toString() {
        $res = $this->getDescription();
        if ($this->health_value !== 0) {
            $res .= PHP_EOL . ($this->isHealEffect() ? 'Heal: ' : 'DMG: ') . $this->deviatingValueToString($this->health_value, $this->healthDeviation);
        }
        if ($this->stress_value !== 0) {
            $res .= PHP_EOL . ($this->isPositiveStressEffect() ? 'Stress Heal: ' : 'Stress: ') . $this->deviatingValueToString($this->stress_value, $this->stressDeviation);
        }
        $this->addEffectRemovalsToString($res);
        if ($this->ignoresArmor) {
            $res .= PHP_EOL . 'Ignores armor.';
        }
        if ($this->hitChance >= 0) {
            $res .= PHP_EOL . 'Accuracy: ' . $this->hitChance;
        }
        if ($this->critChance > 0) {
            $res .= PHP_EOL . 'Crit Mod: +' . $this->critChance;
        }
        if ($this->typeBonuses !== null) {
            foreach ($this->typeBonuses as $typeBonus) {
                $res .= PHP_EOL . $typeBonus->__toString();
            }
        }
        return $res;
    }

    protected function addEffectRemovalsToString(string &$str) {
        $removes = [];
        if ($this->removesDebuff) {
            $removes[] = 'debuff';
        }
        if ($this->removesBlight) {
            $removes[] = 'blight';
        }
        if ($this->removesBleed) {
            $removes[] = 'bleed';
        }
        if ($this->removesStealth) {
            $removes[] = 'stealth';
        }
        $firstRemoved = array_shift($removes);
        if ($firstRemoved !== null) {
            $str .= PHP_EOL . 'Removes: ' . $firstRemoved;
            foreach ($removes as $removed) {
                $str .= ', ' . $removed;
            }
        }
    }

    protected function deviatingValueToString(int $value, int $deviation): string {
        $valueStr = abs($value);
        if ($deviation !== 0) {
            $valueStr .= '-' . abs($value + $deviation);
        }
        return $valueStr;
    }

}