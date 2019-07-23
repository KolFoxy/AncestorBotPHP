<?php

namespace Ancestor\Interaction\Stats;

use Ancestor\Interaction\AbstractLivingBeing;
use Ancestor\Interaction\Hero;

class StatsManager {
    const MAX_BLOCKS = 2;

    /**
     * @var array
     */
    private $stats;

    /**
     * @var StatModifier[]
     */
    public $modifiers = [];

    /**
     * @var StatusEffect[]
     */
    public $statusEffects = [];

    /**
     * @var TypeBonus[]
     */
    public $typeBonuses = [];

    /**
     * @var AbstractLivingBeing|Hero
     */
    public $host;


    public function __construct(AbstractLivingBeing $host, array $statsArray = null) {
        $this->host = $host;
        $this->stats = Stats::getStatsArray();
        if ($statsArray === null) {
            return;
        }
        foreach ($statsArray as $key => $value) {
            if (key_exists($key, $this->stats)) {
                $this->stats[$key] = $value;
            }
        }
    }

    /**
     * @param string $statName
     * @return int|null Stat value or NULL if $statName doesn't exists;
     */
    public function getStatValue(string $statName): int {
        if (key_exists($statName, $this->stats)) {
            return Stats::validateStatValue($this->stats[$statName] + $this->getStatMod($statName), $statName);
        }
        return null;
    }

    private function getStatMod(string $statName): int {
        $value = 0;
        foreach ($this->modifiers as $modifier) {
            if ($modifier->getStat() === $statName) {
                $value += $modifier->value;
            }
        }
        return $value;
    }

    /**
     * @param string $statName
     * @return float
     */
    public function getValueMod(string $statName): float {
        if ($statName === Stats::PROT) {
            return 1.00 - $this->getStatValue($statName) / 100.00;
        }
        return 1.00 + ($this->getStatValue($statName) ?? 0) / 100.00;
    }

    /**
     * @return array|null
     */
    public function getProcessTurn() {
        $values = [];

        foreach ($this->statusEffects as $key => $effect) {
            $type = $effect->getType();
            if ($type === StatusEffect::TYPE_STUN) {
                unset($this->statusEffects[$key]);
                $values[$type] = 1;
                $this->addModifier(StatModifier::getDefaultStunResistBuff());
                continue;
            }

            if ($effect->value !== null) {
                if (isset($values[$type])) {
                    $values[$type] += $effect->value;
                } else {
                    $values[$type] = $effect->value;
                }
            }

            if ($effect->processTurn()) {
                unset($this->statusEffects[$key]);
            }
        }

        foreach ($this->modifiers as $key => $modifier) {
            if ($modifier->processTurn()) {
                unset($this->modifiers[$key]);
            }
        }

        return $this->valuesToFields($values);
    }

    /**
     * @param array $values
     * @return array|null
     */
    function valuesToFields(array $values) {
        $res = [];
        $valueBleed = $values[StatusEffect::TYPE_BLEED] ?? 0;
        $valueBlight = $values[StatusEffect::TYPE_BLIGHT] ?? 0;
        $valueRestoration = $values[StatusEffect::TYPE_RESTORATION] ?? 0;
        $value = $valueRestoration + $valueBleed + $valueBlight;
        if ($value !== 0) {
            $this->host->addHealth($value);
            $effect = $value < 0 ? 'suffered' : 'restored';
            $body = '``' . $this->host->getHealthStatus() . PHP_EOL;
            $body .= $valueBleed !== 0 ? 'Bleed: ' . $valueBleed . PHP_EOL : '';
            $body .= $valueBlight !== 0 ? 'Blight: ' . $valueBlight . PHP_EOL : '';
            $body .= $valueRestoration !== 0 ? 'Restoration: ' . $valueRestoration : '';
            $body .= '``';
            $res[] = [
                'name' => $this->host->name . ' has ' . $effect . ' ' . abs($value) . 'HP',
                'value' => $body,
                'inline' => true];
        }
        if (isset($values[StatusEffect::TYPE_STUN])) {
            $res[] = [
                'name' => $this->host->name . ' is no longer stunned.',
                'value' => 'Current stun resist: ' . $this->getStatValue(Stats::STUN_RESIST),
                'inline' => true];
        }
        if (isset($values[StatusEffect::TYPE_HORROR])) {
            $this->host->addStress($values[StatusEffect::TYPE_HORROR]);
            $res[] = [
                'name' => $this->host->name . ' has suffered ' . $values[StatusEffect::TYPE_HORROR] . ' stress',
                'value' => '``' . $this->host->getStressStatus() . '``'
                    . PHP_EOL . '``' . $this->getStatusEffectState(StatusEffect::TYPE_HORROR) . '``',
                'inline' => true];
        }
        return $res === [] ? null : $res;
    }

    /**
     * @param StatModifier $modifierToAdd
     * @return bool whether the application is successful or not.
     */
    public function addModifier(StatModifier $modifierToAdd): bool {
        if (!$this->checkResists($modifierToAdd)) {
            return false;
        }

        if ($modifierToAdd->isDefaultStunResist()) {
            foreach ($this->modifiers as $statModifier) {
                if ($statModifier->isDefaultStunResist()) {
                    $statModifier->duration = 2;
                    $statModifier->value += 50;
                    return true;
                }
            }
        }

        $this->modifiers[] = $modifierToAdd;
        return true;
    }

    /**
     * @param StatusEffect $effectToAdd
     * @return bool whether the application is successful or not.
     */
    public function addStatusEffect(StatusEffect $effectToAdd): bool {
        if (!$this->checkResists($effectToAdd)) {
            return false;
        }

        if ($effectToAdd->getType() === StatusEffect::TYPE_STUN) {
            foreach ($this->statusEffects as $statusEffect) {
                if ($statusEffect->getType() === StatusEffect::TYPE_STUN && $statusEffect->duration >= 1) {
                    return true;
                }
            }
        }

        if ($effectToAdd->getType() === StatusEffect::TYPE_BLOCK) {
            foreach ($this->statusEffects as $statusEffect) {
                if ($statusEffect->getType() === StatusEffect::TYPE_BLOCK) {
                    $statusEffect->value += $effectToAdd->value;
                    if ($statusEffect->value > self::MAX_BLOCKS) {
                        $statusEffect->value = self::MAX_BLOCKS;
                    }
                    return true;
                }
            }
        }

        $this->statusEffects[] = $effectToAdd;
        return true;
    }

    /**
     * @param TimedEffectInterface $effectToAdd
     * @return bool
     */
    private function checkResists(TimedEffectInterface $effectToAdd): bool {
        if (!$effectToAdd->guaranteedApplication()) {
            $resist = $effectToAdd->getType() . Stats::RESIST_SUFFIX;
            if (key_exists($resist, $this->stats)) {
                $resistValue = $this->stats[$resist] + $this->getStatMod($resist);
                if ($resistValue > 0 && (mt_rand(1, $effectToAdd->getChance()) <= $resistValue)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param string $statusEffectType
     * @return null|string
     */
    public function getStatusEffectState(string $statusEffectType) {
        $combinedEffect = new StatusEffect();
        $combinedEffect->value = 0;
        $combinedEffect->duration = 0;
        foreach ($this->statusEffects as $effect) {
            if ($effect->getType() === $statusEffectType) {
                $combinedEffect->value += $effect->value;
                if ($effect->duration > $combinedEffect->duration || ($effect->duration < 0)) {
                    $combinedEffect->duration = $effect->duration;
                }
            }
        }
        if ($combinedEffect->duration === 0) {
            return null;
        }
        if ($statusEffectType === StatusEffect::TYPE_BLOCK && $combinedEffect->value > self::MAX_BLOCKS) {
            $combinedEffect->value = self::MAX_BLOCKS;
        }
        $combinedEffect->setType($statusEffectType);
        return $combinedEffect->__toString();

    }

    public function getCurrentStatsString(): string {
        $res = '';
        foreach (array_slice(array_keys($this->stats), 0, Stats::DEFAULT_STATS_NUM) as $key) {
            $res .= '``' . Stats::formatName($key) . ': ' . $this->getStatValue($key) . '``' . PHP_EOL;
        }
        return $res;
    }

    public function getAllCurrentEffectsString(): string {
        $res = '';
        foreach ($this->statusEffects as $statusEffect) {
            $res .= '``' . Stats::formatName($statusEffect->getType()) . ': ' . $statusEffect->__toString() . '``' . PHP_EOL;
        }
        foreach ($this->modifiers as $modifier) {
            $res .= '``' . $modifier->__toString() . '``' . PHP_EOL;
        }
        return $res;
    }

    public function isStunned(): bool {
        foreach ($this->statusEffects as $effect) {
            if ($effect->getType() === StatusEffect::TYPE_STUN && !$effect->isDone()) {
                return true;
            }
        }
        return false;
    }

    public function getBonusesVsTarget(AbstractLivingBeing $target): TypeBonus {
        $targetTypes = $target->getAllTypes();
        $res = new TypeBonus();
        foreach ($this->typeBonuses as $typeBonus) {
            if (in_array($typeBonus->type, $targetTypes)) {
                $res->combineWith($typeBonus);
            }
        }
        return $res;
    }

    /**
     * @return string[]
     */
    public function getStatusesNames(): array {
        $res = [];
        foreach ($this->statusEffects as $statusEffect) {
            $res[] = $statusEffect->getType();

        }
        return array_unique($res);
    }

    public function getHealModifier(AbstractLivingBeing $target): int {
        return Stats::validateStatValue(
            $this->getStatValue(Stats::HEAL_SKILL_MOD) + $target->statManager->getStatValue(Stats::HEAL_RECEIVED_MOD),
            Stats::HEAL_SKILL_MOD);
    }

    public function getStressHealModifier(AbstractLivingBeing $target): int {
        return $this->getStatValue(Stats::STRESS_SKILL_MOD) + $target->statManager->getStatValue(Stats::STRESS_HEAL_MOD);
    }

    public function has(string $statusEffectName): bool {
        foreach ($this->statusEffects as $statusEffect) {
            if ($statusEffect->getType() === $statusEffectName) {
                return true;
            }
        }
        return false;
    }

    public function tryBlock(): bool {
        foreach ($this->statusEffects as $key => $effect) {
            if ($effect->getType() === StatusEffect::TYPE_BLOCK) {
                $effect->value--;
                if ($effect->value >= 0) {
                    if ($effect->value === 0) {
                        unset($this->statusEffects[$key]);
                    }
                    return true;
                } else {
                    unset($this->statusEffects[$key]);
                }
            }
        }
        return false;
    }

    public function removeStatusEffectType(string $type): bool {
        $res = false;
        foreach ($this->statusEffects as $key => $statusEffect) {
            if ($statusEffect->getType() === $type) {
                unset($this->statusEffects[$key]);
                $res = true;
            }
        }
        return $res;
    }

    public function removeBleeds(): bool {
        return $this->removeStatusEffectType(StatusEffect::TYPE_BLEED);
    }

    public function removeBlight(): bool {
        return $this->removeStatusEffectType(StatusEffect::TYPE_BLIGHT);
    }

    public function removeDebuffs() {
        foreach ($this->modifiers as $key => $modifier) {
            if ($modifier->getType() === StatModifier::TYPE_DEBUFF) {
                if (is_a($this->host, Hero::class) && $this->host->debuffIsPermanent($key)) {
                    continue;
                }
                unset($this->modifiers[$key]);
            }
        }
    }

    public function isStealthed() : bool {
        return $this->has(StatusEffect::TYPE_STEALTH);
    }

}