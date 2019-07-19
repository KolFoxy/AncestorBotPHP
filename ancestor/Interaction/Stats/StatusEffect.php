<?php

namespace Ancestor\Interaction\Stats;

class StatusEffect implements TimedEffectInterface {

    const TYPE_BLEED = "bleed";
    const TYPE_BLIGHT = "blight";
    const TYPE_HORROR = "horror";
    const TYPE_RESTORATION = "restoration";
    const TYPE_STUN = "stun";
    const TYPE_RIPOSTE = "riposte";
    const TYPE_STEALTH = "stealth";
    const TYPE_MARKED = "marked";
    const TYPE_BLOCK = "block";

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    public $duration = 3;

    /**
     * @var int Negative for guaranteed application.
     */
    public $chance = 100;

    /**
     * @var int|null
     */
    public $value = null;

    /**
     * @var bool Indicates whether or not the effect is applied to the caster no matter the target.
     */
    public $targetSelf = false;

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
            || $type === self::TYPE_RIPOSTE
            || $type === self::TYPE_MARKED
            || $type === self::TYPE_STEALTH) {
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
        if ($this->type === self::TYPE_RIPOSTE
            || $this->type === self::TYPE_RESTORATION
            || $this->type === self::TYPE_BLOCK) {
            return true;
        }
        return false;
    }

    /**
     * @return int
     */
    public function getChance(): int {
        return $this->chance;
    }

    public function __toString(): string {
        if ($this->type === self::TYPE_STUN) {
            return 'Stunned for one turn, unable to perform actions.';
        }
        if ($this->type === self::TYPE_RIPOSTE) {
            return 'Will respond to attacks with a counterattack.';
        }
        if ($this->type === self::TYPE_HORROR) {
            return abs($this->value) . ' stress for ' . $this->duration . 'rds';
        }
        if ($this->type === self::TYPE_BLOCK) {
            return 'Is blocking. Blocks left: ' . $this->value;
        }
        return abs($this->value) . 'pts/rd for ' . $this->duration . 'rds';

    }

    public function guaranteedApplication(): bool {
        return $this->isPositive() || $this->chance < 0 || $this->type === self::TYPE_MARKED;
    }

    public function clone(): StatusEffect {
        $clone = new StatusEffect();
        $clone->duration = $this->duration;
        $clone->value = $this->value;
        $clone->chance = $this->chance;
        $clone->type = $this->type;
        $clone->targetSelf = $this->targetSelf;
        return $clone;
    }

    public function isStun(): bool {
        return $this->type === self::TYPE_STUN;
    }

    public function targetsSelf(): bool {
        return $this->targetSelf;
    }
}