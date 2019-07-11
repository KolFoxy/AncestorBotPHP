<?php

namespace Ancestor\Interaction\Stats;

use Ancestor\Interaction\AbstractLivingBeing;
use Ancestor\Interaction\Hero;

class StressState {

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $quote;

    /**
     * @var StatModifier[]
     */
    protected $statModifiers = [];

    /**
     * @var Hero
     */
    public $host;

    public $isVirtue = false;

    public function __construct(Hero $host, string $name, string $quote) {
        $this->host = $host;
        $this->name = $name;
        $this->quote = $quote;
    }

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
     * @param StatModifier[] $statMods
     */
    public function setStatModifiers(array $statMods) {
        foreach ($statMods as $key => $statModifier) {
            $modKey = $this->name . $key;
            $this->statModifiers[$modKey] = $statModifier;
        }
    }

    /**
     * @return StatModifier[]
     */
    public function getStatModifiers() : array {
        return $this->statModifiers;
    }


}