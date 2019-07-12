<?php

namespace Ancestor\Interaction\Stats;

final class Stats {

    const RESIST_SUFFIX = 'Resist';

    const STUN_RESIST = 'stunResist';
    const BLEED_RESIST = 'bleedResist';
    const BLIGHT_RESIST = 'blightResist';
    const DEBUFF_RESIST = 'debuffResist';
    const DEATHBLOW_RESIST = 'deathblowResist';

    const DAMAGE_MOD = 'damageMod';
    const ACC_MOD = 'accMod';
    const DODGE = 'dodge';
    const PROT = 'prot';
    const CRIT_CHANCE = 'critChance';

    const STRESS_MOD = 'stressMod';
    const STRESS_HEAL_MOD = 'stressHealMod';
    const STRESS_SKILL_MOD = 'stressSkillMod';
    const VIRTUE_CHANCE = 'virtueChance';

    const HEAL_SKILL_MOD = 'healSkillMod';
    const HEAL_RECEIVED_MOD = 'healReceivedMod';

    /**
     * @return array Format: ['stat1' => int, 'stat2' => int, ...]
     */
    public static function getStatsArray(): array {
        return [
            self::STUN_RESIST => 40,
            self::BLEED_RESIST => 30,
            self::BLIGHT_RESIST => 30,
            self::DEBUFF_RESIST => 30,
            self::DEATHBLOW_RESIST => 67,
            self::DAMAGE_MOD => 0,
            self::ACC_MOD => 0,
            self::DODGE => 25,
            self::PROT => 0,
            self::CRIT_CHANCE => 2,
            self::STRESS_MOD => 0,
            self::STRESS_HEAL_MOD => 0,
            self::STRESS_SKILL_MOD => 0,
            self::HEAL_SKILL_MOD => 0,
            self::HEAL_RECEIVED_MOD => 0,
            self::VIRTUE_CHANCE => 25,
        ];
    }

    /**
     * @return string[] Array of stats' names
     */
    public static function getStatNamesArray(): array {
        return array_keys(self::getStatsArray());
    }

    public static function statIsValid(string $stat): bool {
        foreach (self::getStatNamesArray() as $str) {
            if ($stat === $str) {
                return true;
            }
        }
        return false;
    }


    /**
     * @param int $value
     * @param string $statName
     * @return int Validated stat value. Same as the $value param if everything is OK
     */
    public static function validateStatValue(int $value, string $statName): int {
        if ($statName === self::PROT) {
            return $value < 0 ? 0 : $value;
        }
        if ($statName === self::VIRTUE_CHANCE) {
            return $value < 1 ? 1 : $value > 95 ? 95 : $value;
        }
        if ($statName === self::DEATHBLOW_RESIST) {
            return $value > 87 ? 87 : $value;
        }
    }

}