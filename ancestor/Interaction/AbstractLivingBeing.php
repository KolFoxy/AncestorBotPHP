<?php

namespace Ancestor\Interaction;

abstract class AbstractLivingBeing {

    const MISS_MESSAGE = '``...and misses!``';
    const CRIT_MESSAGE = ' ***CRIT!***';

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
     * @var AbstractLivingInteraction;
     */
    public $type;

    /**
     * @return string Format: "currentHealth/healthMax"
     */
    public function getHealthStatus(): string {
        return $this->currentHealth . '/' . $this->healthMax;
    }

    public function getHealthString(): string {
        return 'Health: ' . $this->getHealthStatus();
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
        if (mt_rand(1, $this->type->accuracyMod + $effect->hitChance) <= $target->type->dodge) {
            return false;
        }
        return true;
    }

    public function rollWillCrit(Effect $effect): bool {
        return $effect->canCrit() && mt_rand(0, 100) < ($effect->critChance + $this->type->bonusCritChance);
    }

    public function __construct(AbstractLivingInteraction $type) {
        $this->type = $type;
        $this->currentHealth = $this->healthMax = $type->healthMax;
    }

    /**
     * @param int $value
     */
    abstract public function addHealth(int $value);


}