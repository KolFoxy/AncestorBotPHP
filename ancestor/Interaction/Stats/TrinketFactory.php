<?php

namespace Ancestor\Interaction\Stats;

use Ancestor\Interaction\Hero;

final class TrinketFactory {

    /**
     * @var TrinketFactory|null
     */
    private $instance = null;
    /**
     * @var array
     */
    private $trinkets = [];

    private function __construct() {
        $mapper = new \JsonMapper();
        $mapper->bExceptionOnMissingData = true;
        foreach (glob(dirname(__DIR__, 3) . '/data/rewards/trinkets/*', GLOB_ONLYDIR) as $dir) {
            $rootKey = mb_strtolower(basename($dir));
            $this->trinkets[$rootKey] = [
                Trinket::RARITY_VERY_COMMON => [],
                Trinket::RARITY_COMMON => [],
                Trinket::RARITY_UNCOMMON => [],
                Trinket::RARITY_VERY_RARE => [],
                Trinket::RARITY_ANCESTRAL => [],
                Trinket::RARITY_CRYSTALLINE => [],
                Trinket::RARITY_TROPHY => [],
            ];
            foreach (glob($dir . '/*.json') as $path) {
                $json = json_decode(file_get_contents($path));
                $trinket = new Trinket();
                $mapper->map($json, $trinket);
                $this->trinkets[$rootKey][$trinket->rarity][] = $trinket;
            }
        }
    }

    public static function create(Hero $host): Trinket {

    }


}