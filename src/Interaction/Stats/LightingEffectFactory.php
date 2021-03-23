<?php

namespace Ancestor\Interaction\Stats;

final class LightingEffectFactory {

    /**
     * @var LightingEffect[]
     */
    protected static $lightingEffects;
    /**
     * @var int
     */
    protected static $effectsMaxIndex;

    protected static function populateLightingEffectsArray() {
        if (!isset(self::$lightingEffects)) {
            self::$lightingEffects = [];

            $mapper = new \JsonMapper();
            $mapper->bExceptionOnMissingData = true;
            $mapper->bExceptionOnUndefinedProperty = true;
            $paths = glob(dirname(__DIR__, 3) . '/data/lighting_effects/*.json');
            foreach ($paths as $path) {
                self::$lightingEffects[] = $mapper->map(json_decode(file_get_contents($path)), new LightingEffect());
            }
            self::$effectsMaxIndex = count(self::$lightingEffects) - 1;
        }
    }

    public static function create(): LightingEffect {
        self::populateLightingEffectsArray();
        return self::$lightingEffects[mt_rand(0, self::$effectsMaxIndex)];
    }
}