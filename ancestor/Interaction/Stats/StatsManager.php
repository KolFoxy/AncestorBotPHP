<?php

namespace Ancestor\Interaction\Stats;

use Ancestor\Interaction\AbstractLivingBeing;

class StatsManager {

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
     * @var AbstractLivingBeing
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
     * @return int|bool Stat value or FALSE if $statName doesn't exists;
     */
    public function getStatValue(string $statName): int {
        if (key_exists($statName, $this->stats)) {
            return $this->stats[$statName] + $this->getStatMod($statName);
        }
        return false;
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
     * @return array|null
     */
    public function getProcessTurn() {
        $values = $this->getEmptyValuesArray();

        foreach ($this->statusEffects as $key => $effect) {
            $type = $effect->getType();
            if ($type === StatusEffect::TYPE_STUN) {
                unset($this->statusEffects[$key]);
                $values[$type] = 1;
                $this->addModifier(StatModifier::getDefaultStunResistBuff());
                continue;
            }

            if ($effect->value !== null) {
                $values[$type] += $effect->value;
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

    private function getEmptyValuesArray(): array {
        return [
            StatusEffect::TYPE_STUN => 0,
            StatusEffect::TYPE_BLEED => 0,
            StatusEffect::TYPE_BLIGHT => 0,
            StatusEffect::TYPE_RESTORATION => 0,
            StatusEffect::TYPE_HORROR => 0,
        ];
    }

    /**
     * @param array $values
     * @return array|null
     */
    function valuesToFields(array $values) {
        $res = [];
        $valueBleed = $values[StatusEffect::TYPE_BLEED];
        $valueBlight = $values[StatusEffect::TYPE_BLIGHT];
        $valueRestoration = $values[StatusEffect::TYPE_RESTORATION];
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
        if ($values[StatusEffect::TYPE_STUN]) {
            $res[] = [
                'name' => $this->host->name . ' is no longer stunned.',
                'value' => 'Current stun resist: ' . $this->getStatValue(Stats::STUN_RESIST),
                'inline' => true];
        }
        if ($values[StatusEffect::TYPE_HORROR]) {
            $this->host->addStress($values[StatusEffect::TYPE_HORROR]);
            $res[] = [
                'name' => $this->host->name . ' has suffered ' . $values[StatusEffect::TYPE_HORROR] . ' stress',
                'value' => '``' . $this->host->getStressStatus() . '``',
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

        $this->statusEffects[] = $effectToAdd;
        return true;
    }

    /**
     * @param TimedEffectInterface $effectToAdd
     * @return bool
     */
    private function checkResists(TimedEffectInterface $effectToAdd): bool {
        if (!$effectToAdd->isPositive()) {
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

    public function getAllEffectsState(): string {
        // TODO: Implement the method.
    }

    public function getAllModifiersState(): string {
        $res = [];
        foreach ($this->modifiers as $modifier) {
            if (isset($res[$modifier->getStat()])) {
                $res[$modifier->getStat()] += $modifier->value;
            }
        }
    }

    public function getStatusEffectState(string $statusEffectType): string {
        $combinedEffect = new StatusEffect();
        $combinedEffect->value = 0;
        $combinedEffect->duration = 0;
        foreach ($this->statusEffects as $effect) {
            if ($effect->getType() === $statusEffectType) {
                $combinedEffect->value += $effect->value;
                if ($effect->duration > $combinedEffect->duration) {
                    $combinedEffect->duration = $effect->duration;
                }
            }
        }
        if ($combinedEffect->duration === 0) {
            return '';
        }
        $combinedEffect->setType($statusEffectType);
        return $combinedEffect->__toString();

    }

    public function isStunned(): bool {
        foreach ($this->statusEffects as $effect) {
            if ($effect->getType() === StatusEffect::TYPE_STUN && !$effect->isDone()) {
                return true;
            }
        }
        return false;
    }

}