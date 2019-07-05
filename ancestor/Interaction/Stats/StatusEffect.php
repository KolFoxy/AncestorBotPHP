<?php

namespace Ancestor\Interaction\Stats;

class StatusEffect implements TimedEffectInterface {

    const TYPE_BLEED = "bleed";
    const TYPE_BLIGHT = "bleed";
    const TYPE_HORROR = "horror";
    const TYPE_STUN = "stun";
    const TYPE_RESTORATION = "restoration";
    const TYPE_RIPOSTE = "riposte";

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    public $duration = 3;

    /**
     * @var int
     */
    public $chance = 100;

    /**
     * @var int
     */
    public $value;

    /**
     * @param string $type
     * @throws \Exception
     */
    public function setType(string $type) {
        if ($type === self::TYPE_BLEED
            || $type === self::TYPE_BLIGHT
            || $type === self::TYPE_STUN
            || $type === self::TYPE_HORROR
            || $type === self::TYPE_RESTORATION
            || $type === self::TYPE_RIPOSTE) {
            $this->type = $type;
            return;
        }
        throw new \Exception('Invalid StatusEffect type');
    }


    /**
     * @return string
     */
    public function getType(): string {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function processTurn(): bool {
        $this->duration--;
        return $this->isDone();
    }

    /**
     * @inheritdoc
     */
    public function isDone(): bool {
        if ($this->duration <= 0) {
            return true;
        }
        return false;
    }

    public function isPositive(): bool {
        if ($this->type === self::TYPE_RIPOSTE || $this->type === self::TYPE_RESTORATION) {
            return true;
        }
        return false;
    }

}