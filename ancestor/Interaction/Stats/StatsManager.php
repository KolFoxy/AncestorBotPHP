<?php

namespace Ancestor\Interaction\Stats;

use Ancestor\Interaction\AbstractLivingBeing;
use Ancestor\Interaction\Hero;
use function Sodium\add;

class StatsManager {

    /**
     * @var array
     */
    private $stats;

    /**
     * @var StatModifier[]
     */
    public $modifiers;

    /**
     * @var StatusEffect[]
     */
    public $statusEffects;

    /**
     * @var AbstractLivingBeing|Hero
     */
    public $host;

    public function __construct(array $statsArray = null) {
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

    public function processTurn(): array {
        $values = [StatusEffect::TYPE_RIPOSTE => true];
        foreach ($this->statusEffects as $key => $effect) {
            $type = $effect->getType();
            if ($type === StatusEffect::TYPE_STUN) {
                unset($this->statusEffects[$key]);
                $this->addModifier(StatModifier::getDefaultStunResistBuff());
                continue;
            }

            if ($type !== StatusEffect::TYPE_RIPOSTE) {
                //TODO : field array returning
            } else {
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
    }

    public function addModifier(StatModifier $modifier) {
        // TODO: Implement addModifier the method
    }

    public function getEffectsStatus() {
        // TODO: Implement addModifier the method.
    }

}