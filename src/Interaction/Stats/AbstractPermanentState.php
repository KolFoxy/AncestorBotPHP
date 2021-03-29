<?php

namespace Ancestor\Interaction\Stats;

use Ancestor\Interaction\AbstractLivingBeing;

abstract class AbstractPermanentState {

    /**
     * @var string
     * @required
     */
    public string $name;

    /**
     * @var StatModifier[]
     */
    protected array $statModifiers = [];

    public function apply(AbstractLivingBeing $host): void {
        foreach ($this->statModifiers as $key => $statModifier) {
            $host->statManager->modifiers[$key] = $statModifier;
        }
    }

    public function remove(AbstractLivingBeing $host): void {
        foreach ($this->statModifiers as $key => $statModifier) {
            unset($host->statManager->modifiers[$key]);
        }
    }

    /**
     * @param StatModifier[] $statModifiers
     */
    public function setStatModifiers(array $statModifiers): void {
        foreach ($statModifiers as $key => $statModifier) {
            $modKey = $this->name . $key;
            $this->statModifiers[$modKey] = $statModifier;
        }
    }

    /**
     * @return StatModifier[]
     */
    public function getStatModifiers(): array {
        return $this->statModifiers;
    }

    public function keyIsInModifiers(string $key): bool {
        foreach (array_keys($this->statModifiers) as $arrayKey) {
            if ($key === $arrayKey) {
                return true;
            }
        }
        return false;
    }

}