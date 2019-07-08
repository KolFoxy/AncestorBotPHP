<?php

namespace Ancestor\Interaction;

use Ancestor\Interaction\Stats\Stats;
use Ancestor\Interaction\Stats\StatsManager;

abstract class AbstractLivingBeing {

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
     * @var bool
     */
    public $isStunned = false;

    /**
     * @var StatsManager;
     */
    public $statManager;

    /**
     * @var AbstractLivingInteraction;
     */
    public $type;

    /**
     * @return string Format: "Health: currentHealth/healthMax"
     */
    public function getHealthStatus(): string {
        return 'Health: ' . $this->currentHealth . '/' . $this->healthMax;
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
        if (mt_rand(1, $this->statManager->getStatValue(Stats::ACC_MOD) + $effect->hitChance)
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
        $this->statManager = new StatsManager($type->stats);
    }

    /**
     * @param int $value
     */
    abstract public function addHealth(int $value);


}