<?php

namespace Ancestor\Interaction\Stats;

use Ancestor\Interaction\Hero;

abstract class AbstractPermanentState {

    /**
     * @var string
     * @required
     */
    public $name;

    /**
     * @var StatModifier[]
     */
    protected $statModifiers = [];

    /**
     * @var Hero
     */
    public $host;

    public function apply() {
        foreach ($this->statModifiers as $key => $statModifier) {
            $this->host->statManager->modifiers[$key] = $statModifier;
        }
    }

    public function remove() {
        foreach ($this->statModifiers as $key => $statModifier) {
            unset($this->host->statManager->modifiers[$key]);
        }
    }

    /**
     * @param StatModifier[] $statModifiers
     */
    public function setStatModifiers(array $statModifiers) {
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

}