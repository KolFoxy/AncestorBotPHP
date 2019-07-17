<?php

namespace Ancestor\Interaction;

use Ancestor\Interaction\Stats\Stats;
use Ancestor\Interaction\Stats\TypeBonus;

class DirectActionEffect extends AbstractEffect {


    /**
     * Chance of crit for the effect. negative - can't crit.
     * @var int
     */
    public $critChance = 0;

    /**
     * @var int Negative for guarantee hit
     */
    public $hitChance = 100;

    /**
     * @var \Ancestor\Interaction\Stats\TypeBonus[]|null
     */
    public $typeBonuses = null;

    /**
     * @var bool
     */
    public $ignoresArmor = false;

    /**
     * @var bool
     */
    public $removesStealth = false;

    public function canCrit(): bool {
        return $this->critChance < 0;
    }

    /**
     * @param AbstractLivingBeing $caster Who is trying to apply the effect on $target
     * @param AbstractLivingBeing $target The object of the effect.
     * @return array [ 'hit' => bool, 'crit' => bool, 'healthValue' => int, 'stressValue' => int ]
     */
    public function applyToTarget(AbstractLivingBeing $caster, AbstractLivingBeing $target): array {
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
        $res['crit'] = $this->rollWillCrit($caster, $typeBonus->critChanceMod);

        $critValueMod = $res['crit'] ? 1.5 : 1.00;
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
    }

    protected function getDamageModifier(AbstractLivingBeing $caster, AbstractLivingBeing $target, int $modifier): float {
        $modifier += $caster->statManager->getStatValue(Stats::DAMAGE_MOD);
        $protMod = $this->ignoresArmor ? 1.00 : $target->statManager->getValueMod(Stats::PROT);
        return (1.00 + $modifier / 100.00) * $protMod;
    }

    public function getTotalTypeBonus(AbstractLivingBeing $caster, AbstractLivingBeing $target): TypeBonus {
        $res = $caster->statManager->getBonusesVsTarget($target);
        $targetTypes = $target->getAllTypes();
        foreach ($this->typeBonuses as $bonus) {
            if (in_array($bonus->type, $targetTypes)) {
                $res->combineWith($bonus);
            }
        }
        return $res;
    }

    protected function rollWillCrit(AbstractLivingBeing $caster, int $modifier): bool {
        return $this->canCrit() && mt_rand(1, 100) <=
            ($this->critChance + $modifier + $caster->statManager->getStatValue(Stats::CRIT_CHANCE));
    }

    protected function rollWillHit(AbstractLivingBeing $caster, AbstractLivingBeing $target, int $modifier): bool {
        $accuracy = 5 + $this->hitChance + $caster->statManager->getStatValue(Stats::ACC_MOD) + $modifier
            - $target->statManager->getStatValue(Stats::DODGE);
        if (mt_rand(1, 100) <= $accuracy) {
            return true;
        }
        return false;
    }


}