<?php

namespace Ancestor\Interaction\Stats;

use Ancestor\Interaction\Hero;

final class TrinketFactory {

    const SHARED_KEY = 'shared';
    const GROUP_FIRST = 0;
    const GROUP_SECOND = 1;
    const GROUP_THIRD = 2;

    /**
     * @var array|null
     */
    private static $trinketPaths = null;

    private static function setTrinketPaths() {
        $mapper = new \JsonMapper();
        $mapper->bExceptionOnMissingData = true;
        foreach (glob(dirname(__DIR__, 3) . '/data/rewards/trinkets/*', GLOB_ONLYDIR) as $dir) {
            $rootKey = mb_strtolower(basename($dir));
            self::$trinketPaths[$rootKey] = [
                self::GROUP_FIRST => [],
                self::GROUP_SECOND => [],
                self::GROUP_THIRD => [],
            ];
            foreach (glob($dir . '/*.json') as $path) {
                $json = json_decode(file_get_contents($path), true);
                self::$trinketPaths[$rootKey][self::numToGroupId($json['rarity'])][] = $path;
            }
        }
    }

    public static function create(Hero $host): Trinket {
        if (self::$trinketPaths === null) {
            self::setTrinketPaths();
        }
        $group = mt_rand(1, 100);
        if ($group <= 50) {
            $group = self::GROUP_FIRST;
        } elseif ($group >= 80) {
            $group = self::GROUP_THIRD;
        } else {
            $group = self::GROUP_SECOND;
        }
        $possibleTrinkets = array_merge(
            self::$trinketPaths[self::SHARED_KEY][$group],
            self::$trinketPaths[mb_strtolower($host->type->name)][$group]
        );
        $json = json_decode(file_get_contents($possibleTrinkets[mt_rand(0, count($possibleTrinkets) - 1)]));

        $mapper = new \JsonMapper();
        $mapper->bExceptionOnMissingData = true;
        $res = new Trinket();
        $mapper->map($json, $res);
        $res->host = $host;
        return $res;
    }

    private static function numToGroupId(int $num): int {
        if ($num <= Trinket::RARITY_UNCOMMON) {
            return self::GROUP_FIRST;
        }
        if ($num >= Trinket::RARITY_ANCESTRAL) {
            return self::GROUP_THIRD;
        }
        return self::GROUP_SECOND;
    }


}