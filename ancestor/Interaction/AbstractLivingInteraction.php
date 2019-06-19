<?php

namespace Ancestor\Interaction;

abstract class AbstractLivingInteraction extends AbstractInteraction {

    /**
     * @var int
     * @required
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


}