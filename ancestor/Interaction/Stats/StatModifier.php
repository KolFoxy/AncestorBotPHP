<?php

namespace Ancestor\Interaction\Stats;

class StatModifier implements TimedEffectInterface {

    /**
     * Used for identifying the default stun resist buff to allow stacking.
     */
    const DEF_STUN_RESIST_CHANCE = -12531758;


    /**
     * @var int Chance to hit. Negative value indicates positive effects (buffs).
     * @required
     */
    public $chance = 100;

    /**
     * @var string Stat that is being modified
     */
    private $stat;

    /**
     * @var int
     * @required
     */
    public $value = 0;

    /**
     * @var int 0 means it's done, negative value means it's eternal.
     * @required
     */
    public $duration = 3;

    /**
     * @var bool Indicates whether or not the modifier is applied to the caster no matter the target.
     */
    public $targetSelf = false;

    /**
     * @param string $stat Set the stat that is modified by this StatModifier
     * @throws \Exception
     */
    public function setStat(string $stat) {
        if (!Stats::statIsValid($stat)) {
            throw new \Exception('Invalid stat name for a StatModifier.');
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
        if ($this->isDone()) {
            return true;
        }
        if ($this->duration < 0) {
            return false;
        }
        $this->duration--;
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
        return $this->chance < 0;
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
        return $this->isPositive() ? 'buff' : 'debuff';
    }


    /**
     * @return int
     */
    public function getChance(): int {
        return $this->chance;
    }
}