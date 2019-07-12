<?php

namespace Ancestor\Interaction\Stats;

use Ancestor\Interaction\Hero;
use Ancestor\RandomData\RandomDataProvider;

final class StressStateFactory {

    public static function create(Hero $host): StressState {
        $resolve = RandomDataProvider::GetInstance()->GetRandomResolve($host->statManager->getStatValue(Stats::VIRTUE_CHANCE));
        $res = new StressState($host, $resolve['name'], $resolve['quote']);
        $modsFilename = dirname(__DIR__, 3) . '/data/stress_modifiers/' . $res->name . '.json';
        if (file_exists($modsFilename)) {
            $mapper = new \JsonMapper();
            $json = json_decode(file_get_contents($modsFilename));
            $mapper->bExceptionOnMissingData = true;
            $res->setStatModifiers($mapper->mapArray($json, [], StatModifier::class));
        } else {
            echo PHP_EOL . 'ATTENTION: File ' . $res->name . '.json is not found.' . PHP_EOL;
        }
        return $res;
    }
}