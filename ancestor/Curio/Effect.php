<?php

namespace Ancestor\Curio;

use Ancestor\ImageTemplate\ImageTemplate;

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
     * ImageTemplate to use with the $image var.
     * @var ImageTemplate|null
     */
    public $imageTemplate = null;

    /**
     * Path/URL to the image.
     * @var string|null
     */
    public $image = null;


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

    public function hasImage(){
        return isset($this->image) && isset($this->imageTemplate);
    }
}