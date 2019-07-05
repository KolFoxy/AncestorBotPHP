<?php

namespace Ancestor\Interaction\Stats;

class Stats {

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

    /**
     * @return array Format: ['stat1' => 0, 'stat2' => 0]
     */
    public static function getStatsArray(): array {
        return [
            self::STUN_RESIST => 0,
            self::BLEED_RESIST => 0,
            self::BLIGHT_RESIST => 0,
            self::DEBUFF_RESIST => 0,
            self::DEATHBLOW_RESIST => 0,
            self::DAMAGE_MOD => 0,
            self::ACC_MOD => 0,
            self::DODGE => 0,
            self::PROT => 0,
            self::CRIT_CHANCE => 0,
            self::STRESS_MOD => 0,
            self::STRESS_HEAL_MOD => 0,
        ];
    }

    /**
     * @return string[] Array of stats' names
     */
    public static function getStatNamesArray(): array {
        return [
            self::STUN_RESIST,
            self::BLEED_RESIST,
            self::BLIGHT_RESIST,
            self::DEBUFF_RESIST,
            self::DEATHBLOW_RESIST,
            self::DAMAGE_MOD,
            self::ACC_MOD,
            self::DODGE,
            self::PROT,
            self::CRIT_CHANCE,
            self::STRESS_MOD,
            self::STRESS_HEAL_MOD,
        ];
    }

    public static function statIsValid(string $stat): bool {
        foreach (self::getStatNamesArray() as $str) {
            if ($stat === $str) {
                return true;
            }
        }
        return false;
    }

}