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
     * @return int
     */

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
        return $this->getCurrentHealth().'/'.$this->healthMax;
    }

    /**
     * @return bool
     */
    public function isDead(): bool {
        return $this->getCurrentHealth() <= 0;
    }



}