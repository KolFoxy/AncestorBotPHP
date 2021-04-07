<?php

namespace Ancestor\RandomData;

class RandomDataProvider {
    private array $NSFWquotes = [];
    private array $gold = [];
    private array $trinkets = [];
    private array $rewardsQuotes = [];
    private array $quirksPositive = [];
    private array $quirksNegative = [];
    private array $heroDiesQuotes = [];
    private array $monsterDiesQuotes = [];

    private static ?RandomDataProvider $instance = null;
    const rewardTrinketChance = 30;

    private function __construct() {
        $this->populateArray($this->rewardsQuotes, '/data/rewards/rewardsQuotes');
        $this->populateArray($this->gold, '/data/gold');
        $this->populateArray($this->trinkets, '/data/trinkets');
        $this->populateArray($this->NSFWquotes, '/data/NSFWquotes');
        $this->populateArray($this->quirksNegative, '/data/quirksNegative');
        $this->populateArray($this->quirksPositive, '/data/quirksPositive');
        $this->populateArray($this->heroDiesQuotes, '/data/heroDiesQuotes');
        $this->populateArray($this->monsterDiesQuotes, '/data/monsterDiesQuotes');
    }

    /**
     * @return RandomDataProvider
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new RandomDataProvider();
        }
        return self::$instance;
    }

    public function getRandomData($array) {
        return $array[mt_rand(0, sizeof($array) - 1)];
    }


    public function getRandomHeroDeathQuote(): string {
        return $this->getRandomData($this->heroDiesQuotes);
    }

    public function getRandomMonsterDeathQuote(): string {
        return $this->getRandomData($this->monsterDiesQuotes);
    }

    public function getRandomNSFWQuote() {
        return $this->getRandomData($this->NSFWquotes);
    }

    public function getRandomPositiveQuirk() {
        return $this->getRandomData($this->quirksPositive);
    }

    public function getRandomNegativeQuirk() {
        return $this->getRandomData($this->quirksNegative);
    }

    public function getRandomReward() {
        if (mt_rand(1, 100) <= self::rewardTrinketChance) {
            return $this->getRandomData($this->trinkets);
        }
        return $this->getRandomData($this->gold);
    }

    public function getRandomRewardQuote() {
        return $this->getRandomData($this->rewardsQuotes);
    }

    private function populateArray(&$array, $file_path, $isJson = false) {
        if (!empty($array)) {
            return;
        }
        if (!$isJson) {
            $array = file(dirname(__DIR__, 2) . $file_path);
            return;
        }
        $array = json_decode(file_get_contents(dirname(__DIR__, 2) . $file_path), true);
    }
}
