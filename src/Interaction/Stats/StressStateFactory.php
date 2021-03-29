<?php

namespace Ancestor\Interaction\Stats;

use JsonMapper;

final class StressStateFactory {

    /**
     * @var null|string[]
     */
    private static ?array $afflictionQuotes = null;

    private static int $afflictionQuotesMaxIndex = 0;

    /**
     * @var array
     */
    private static array $stressStates = [
        'virtues' => [],
        'afflictions' => [],
    ];

    /**
     * @var bool
     */
    private static bool $arrayIsPopulated = false;

    private static int $virtuesMaxIndex = 0;
    private static int $afflictionsMaxIndex = 0;

    public static function create(int $virtueChance = 25): StressState {
        self::populateStatesArray();
        if (mt_rand(0, 100) <= $virtueChance) {
            return self::$stressStates['virtues'][mt_rand(0, self::$virtuesMaxIndex)];
        }
        return self::$stressStates['afflictions'][mt_rand(0, self::$afflictionsMaxIndex)];
    }

    private static function populateStatesArray(): void {
        if (self::$arrayIsPopulated) {
            return;
        }
        $mapper = new JsonMapper();
        $mapper->bExceptionOnMissingData = true;
        $mapper->bExceptionOnUndefinedProperty = true;
        $paths = glob(dirname(__DIR__, 3) . '/data/stress_modifiers/*.json');
        foreach ($paths as $path) {
            $state = new StressState();
            $json = json_decode(file_get_contents($path));
            $mapper->map($json, $state);
            if ($state->isVirtue) {
                self::$stressStates['virtues'][] = $state;
                continue;
            }
            self::$stressStates['afflictions'][] = $state;
        }
        self::$virtuesMaxIndex = count(self::$stressStates['virtues']) - 1;
        self::$afflictionsMaxIndex = count(self::$stressStates['afflictions']) - 1;
        self::$arrayIsPopulated = true;
    }

    public static function getRandomAfflictionQuote(): string {
        if (self::$afflictionQuotes === null) {
            self::$afflictionQuotes = file(dirname(__DIR__, 3) . '/data/afflictions');
            self::$afflictionQuotesMaxIndex = count(self::$afflictionQuotes) - 1;
        }
        return self::$afflictionQuotes[mt_rand(0, self::$afflictionQuotesMaxIndex)];
    }
}