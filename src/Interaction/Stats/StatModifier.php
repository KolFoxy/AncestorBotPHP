<?php

namespace Ancestor\Interaction\Stats;

use Exception;

class StatModifier implements TimedEffectInterface {

    const TYPE_DEBUFF = 'debuff';
    const TYPE_BUFF = 'buff';

    /**
     * Used for identifying the default stun resist buff to allow stacking.
     */
    const DEF_STUN_RESIST_CHANCE = -12531758;


    /**
     * @var int Chance to hit. Negative value indicates guaranteed application.
     */
    public int $chance = 100;

    /**
     * @var string Stat that is being modified
     */
    private string $stat;

    /**
     * @var int
     * @required
     */
    public int $value = 0;

    /**
     * @var int 0 means it's done, negative value means it's eternal.
     */
    public int $duration = 3;

    /**
     * @var bool Indicates whether or not the modifier is applied to the caster no matter the target.
     */
    public bool $targetSelf = false;

    /**
     * @param string $stat Set the stat that is modified by this StatModifier
     * @throws Exception
     */
    public function setStat(string $stat): void {
        if (!Stats::statIsValid($stat)) {
            throw new Exception('Invalid stat name for a StatModifier. "' . $stat . '"');
        }
        $this->stat = $stat;
    }

    /**
     * @return string
     */
    public function getStat(): string {
        return $this->stat;
    }

    /**
     * @inheritdoc
     */
    public function processTurn(): bool {
        if ($this->duration > 0) {
            $this->duration--;
        }
        return $this->isDone();
    }

    /**
     * @inheritdoc
     */
    public function isDone(): bool {
        if ($this->duration === 0) {
            return true;
        }
        return false;
    }

    public function isPositive(): bool {
        if ($this->stat === Stats::STRESS_MOD || $this->stat === Stats::CRIT_RECEIVED_CHANCE || $this->stat === Stats::DAMAGE_TAKEN_MOD) {
            return $this->value < 0;
        }
        return $this->value > 0;
    }

    public static function getDefaultStunResistBuff(): StatModifier {
        $statMod = new StatModifier();
        $statMod->duration = 2;
        $statMod->chance = self::DEF_STUN_RESIST_CHANCE;
        $statMod->setStat(Stats::STUN_RESIST);
        $statMod->value = 50;
        return $statMod;
    }

    public function isDefaultStunResist(): bool {
        return $this->stat === Stats::STUN_RESIST && $this->chance === self::DEF_STUN_RESIST_CHANCE;
    }

    /**
     * @return string Return the type of the StatModifier (buff/debuff).
     */
    public function getType(): string {
        return $this->isPositive() ? self::TYPE_BUFF : self::TYPE_DEBUFF;
    }


    /**
     * @return int
     */
    public function getChance(): int {
        return $this->chance;
    }

    public function clone(): StatModifier {
        $clone = new StatModifier();
        $clone->duration = $this->duration;
        $clone->value = $this->value;
        $clone->chance = $this->chance;
        $clone->stat = $this->stat;
        $clone->targetSelf = $this->targetSelf;
        return $clone;
    }

    public function __toString(): string {
        return Stats::formatName($this->stat) . ': ' . ($this->value < 0 ? '' : '+') . $this->value
            . ($this->duration > 0 ? ' for ' . $this->duration . 'rds' : '');
    }

    public function guaranteedApplication(): bool {
        return $this->chance < 0;
    }

    public function targetsSelf(): bool {
        return $this->targetSelf;
    }
}