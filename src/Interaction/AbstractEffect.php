<?php

namespace Ancestor\Interaction;

use Exception;

abstract class AbstractEffect {

    const RAND_NUM_DESCRIPTION_NEEDLE = '{RAND_NUM}(';
    const RNUM_NEEDLE_LENGTH = 11;
    const INVALID_EFFECT_DESCRIPTION_MSG = 'Invalid description type for an Effect.';

    /**
     * Indicates the amount of stress that effect gives hero.
     * @var int|null
     */
    public ?int $stress_value = 0;

    /**
     * @var int Indicates how much stress value should deviate UP from the base.
     */
    public int $stressDeviation = 0;

    /**
     * Indicates the amount of hp that effect gives or subtracts from hero.
     * @var int|null
     */
    public ?int $health_value = 0;

    /**
     * @var int Indicates how much health value should deviate UP from the base.
     */
    public int $healthDeviation = 0;

    /**
     * @var string|string[]
     */
    protected $_description;

    /**
     * Path to the template.
     * @var string|null
     */
    public ?string $imageTemplate = null;

    /**
     * Path to the image.
     * @var string|null
     */
    public ?string $image = null;


    /**
     * @var bool
     */
    public bool $removesBlight = false;
    /**
     * @var bool
     */
    public bool $removesBleed = false;
    /**
     * @var bool
     */
    public bool $removesDebuff = false;


    /**
     * @return bool
     */
    public function isPositiveStressEffect(): bool {
        return $this->stress_value < 0;
    }

    /**
     * @return bool
     */
    public function isNegativeStressEffect(): bool {
        return $this->stress_value > 0;
    }

    /**
     * @return bool
     */
    public function isDamageEffect(): bool {
        return $this->health_value < 0;
    }

    /**
     * @return bool
     */
    public function isHealEffect(): bool {
        return $this->health_value > 0;
    }


    public function hasImage() {
        return isset($this->image) && isset($this->imageTemplate);
    }

    /**
     * @return string Returns description param or a random one from its array.
     */
    public function getDescription(): string {
        if (!is_array($this->_description)) {
            return $this->_description;
        }
        return $this->_description[mt_rand(0, sizeof($this->_description) - 1)];
    }

    /**
     * @param mixed $description Accepts either an array or a string
     * @throws Exception
     */
    public function setDescription($description) {
        if (!is_string($description)) {
            if (!is_array($description)) {
                throw new Exception(self::INVALID_EFFECT_DESCRIPTION_MSG);
            }
            foreach ($description as $item) {
                if (!is_string($item)) {
                    throw new Exception(self::INVALID_EFFECT_DESCRIPTION_MSG);
                }
            }
        }
        $this->_description = $description;
    }

    /**
     * Replaces {RAND_NUM}(MIN_MAX) with corresponding random number;
     * @param string $string
     * @return string
     */
    public static function parseRandomNum(string $string): string {
        if (($start = mb_strpos($string, self::RAND_NUM_DESCRIPTION_NEEDLE)) === false) {
            return $string;
        }
        $startMin = $start + self::RNUM_NEEDLE_LENGTH;
        $finishMin = mb_strpos($string, '_', $startMin);
        if ($finishMin === false) {
            return $string;
        }

        $startMax = $finishMin + 1;
        $finish = mb_strpos($string, ')', $startMax);
        if ($finish === false) {
            return $string;
        }

        $res = mt_rand(
            intval(mb_substr($string, $startMin, $finishMin - 1)),
            intval(mb_substr($string, $startMax, $finish - 1))
        );

        $string = substr_replace($string, $res, $start, $finish - $start + 1);

        return self::parseRandomNum($string);
    }

    public function getHealthValue(): int {
        return $this->getDeviatingValue($this->health_value, $this->healthDeviation);
    }

    public function getStressValue(): int {
        return $this->getDeviatingValue($this->stress_value, $this->stressDeviation);
    }

    /**
     * @param int|null $value
     * @param int $deviation
     * @return int
     */
    protected function getDeviatingValue(int $value, int $deviation) {
        if ($value === null) {
            return 0;
        }
        $valueMax = $value + $deviation;
        $valueMin = $value < $valueMax ? $value : $valueMax;
        $valueMax = $valueMax < $value ? $value : $valueMax;
        return mt_rand($valueMin, $valueMax);
    }



}