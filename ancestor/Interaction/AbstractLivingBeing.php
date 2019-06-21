<?php

namespace Ancestor\Interaction;

abstract class AbstractLivingBeing {

    /**
     * @var int
     */
    public $healthMax;

    /**
     * @var int|null
     */
    protected $currentHealth = null;

    /**
     * @var bool
     */
    public $isStunned = false;

    /**
     * @var int
     */
    public $bonusCritChance = 5;

    /**
     * @var AbstractLivingInteraction;
     */
    public $type;

    public function getCurrentHealth(): int {
        if ($this->currentHealth === null) {
            $this->currentHealth = $this->healthMax;
        }
        return $this->currentHealth;
    }

    /**
     * @return string Format: "currentHealth/healthMax"
     */
    public function getHealthStatus(): string {
        return $this->getCurrentHealth() . '/' . $this->healthMax;
    }

    /**
     * @return bool
     */
    public function isDead(): bool {
        return $this->getCurrentHealth() <= 0;
    }

    /**
     * @param AbstractLivingBeing $target
     * @return bool
     */
    public function rollWillHit(AbstractLivingBeing $target): bool {
        if (mt_rand(1, $this->type->accuracy) <= $target->type->dodge) {
            return false;
        }
        return true;
    }

    public function rollWillCrit(Effect $effect): bool {
        return $effect->canCrit() && mt_rand(0, 100) < ($effect->critChance + $this->bonusCritChance);
    }

    public function __construct(AbstractLivingInteraction $type) {
        $this->type = $type;
        $this->healthMax = $type->healthMax;
    }


}