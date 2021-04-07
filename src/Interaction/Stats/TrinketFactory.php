<?php

namespace Ancestor\Interaction\Stats;

use Ancestor\Interaction\Hero;
use JsonMapper;

final class TrinketFactory {

    const SHARED_KEY = 'shared';
    const GROUP_FIRST = 0;
    const GROUP_SECOND = 1;
    const GROUP_THIRD = 2;

    /**
     * @var array|null
     */
    private static ?array $trinkets = null;

    /**
     * @var Trinket|null
     */
    private static ?Trinket $defaultTrinket = null;

    private static function setTrinkets(): void {
        if (self::$trinkets !== null) {
            return;
        }
        self::$trinkets = [];
        $mapper = new JsonMapper();
        $mapper->bExceptionOnMissingData = true;
        $mapper->bExceptionOnUndefinedProperty = true;
        foreach (glob(dirname(__DIR__, 3) . '/data/rewards/trinkets/*', GLOB_ONLYDIR) as $dir) {
            $className = str_replace('_', ' ', mb_strtolower(basename($dir)));
            self::$trinkets[$className] = [
                self::GROUP_FIRST => [],
                self::GROUP_SECOND => [],
                self::GROUP_THIRD => [],
            ];
            foreach (glob($dir . '/*.json') as $path) {
                $json = json_decode(file_get_contents($path));
                $trinket = new Trinket();
                $mapper->map($json, $trinket);
                self::$trinkets[$className][self::numToGroupId($trinket->rarity)][] = $trinket;
            }
        }
        $path = dirname(__DIR__, 3) . '/data/rewards/trinkets/shared/book_of_sanity.json';
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        self::$defaultTrinket = $mapper->map(json_decode(file_get_contents($path)), new Trinket());
    }

    public static function create(Hero $host): Trinket {
        self::setTrinkets();

        $group = mt_rand(1, 100);
        if ($group <= 50) {
            $group = self::GROUP_FIRST;
        } elseif ($group >= 80) {
            $group = self::GROUP_THIRD;
        } else {
            $group = self::GROUP_SECOND;
        }

        $className = mb_strtolower($host->type->name);
        $possibleTrinkets = array_merge(
            self::$trinkets[self::SHARED_KEY][$group] ?? [],
            self::$trinkets[$className][$group] ?? []);

        $size = count($possibleTrinkets);
        return $size === 0
            ? self::$defaultTrinket
            : $possibleTrinkets[mt_rand(0, $size - 1)];
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