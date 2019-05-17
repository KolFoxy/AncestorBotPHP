<?php

namespace Ancestor\Curio;

class Effect {
    /**
     * @var string
     * @required
     */
    public $name;

    /**
     * @var string
     * @required
     */
    public $description;

    /**
     * Indicates the amount of stress that effect gives hero.
     * @var int|null
     */
    public $stress_value = null;

    /**
     * Indicates whether or not effect gives hero positive(TRUE) or negative(FALSE) quirk.
     * @var bool|null
     */
    public $quirk_positive = null;

    /**
     * @return bool
     */
    public function isPositiveStressEffect(): bool {
        return isset($this->stress_value) && $this->stress_value > 0;
    }

    /**
     * @return bool
     */
    public function isNegativeStressEffect(): bool {
        return isset($this->stress_value) && $this->stress_value < 0;
    }

    /**
     * @return bool
     */
    public function isPositiveQuirkEffect(): bool {
        return isset($this->stress_value) && $this->quirk_positive;
    }

    /**
     * @return bool
     */
    public function isNegativeQuirkEffect(): bool {
        return isset($this->stress_value) && !$this->quirk_positive;
    }
}