<?php

namespace Ancestor\RandomData;

class RandomDataProvider {
    private $afflictions;
    private $virtues;
    private $NSFWquotes;
    private $gold;
    private $trinkets;
    private $rewardsQuotes;
    private $quirksPositive;
    private $quirksNegative;
    private $heroDiesQuotes;
    private $monsterDiesQuotes;

    private static $instance = null;

    const virtueChance = 25;
    const rewardTrinketChance = 30;

    private function __construct() {
        $this->PopulateArray($this->rewardsQuotes, '/data/rewards/rewardsQuotes');
        $this->PopulateArray($this->gold, '/data/rewards/gold');
        $this->PopulateArray($this->trinkets, '/data/rewards/trinkets');
        $this->PopulateArray($this->virtues, '/data/virtues.json', true);
        $this->PopulateArray($this->NSFWquotes, '/data/NSFWquotes');
        $this->PopulateArray($this->afflictions, '/data/afflictions.json', true);
        $this->PopulateArray($this->quirksNegative, '/data/quirksNegative');
        $this->PopulateArray($this->quirksPositive, '/data/quirksPositive');
        $this->PopulateArray($this->heroDiesQuotes, '/data/heroDiesQuotes');
        $this->PopulateArray($this->monsterDiesQuotes,'/data/monsterDiesQuotes');
    }

    /**
     * @return RandomDataProvider
     */
    public static function GetInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new RandomDataProvider();
        }
        return self::$instance;
    }

    public function GetRandomData($array) {
        return $array[mt_rand(0, sizeof($array) - 1)];
    }

    private function GetRandomAffliction() {
        $affliction = $this->GetRandomData($this->afflictions['afflictions']);
        if (mt_rand(1, 100) <= 50) {
            $affliction['quote'] = $this->GetRandomData($this->afflictions['quotes']);
        }
        return $affliction;
    }

    public function GetRandomHeroDeathQuote() : string {
        return $this->GetRandomData($this->heroDiesQuotes);
    }

    public function GetRandomMonsterDeathQuote() : string {
        return $this->GetRandomData($this->monsterDiesQuotes);
    }

    public function GetRandomResolve() {
        if (mt_rand(1, 100) <= self::virtueChance) {
            return $this->GetRandomData($this->virtues['virtues']);
        }
        return $this->GetRandomAffliction();
    }

    public function GetRandomNSFWQuote() {
        return $this->GetRandomData($this->NSFWquotes);
    }

    public function GetRandomPositiveQuirk(){
        return $this->GetRandomData($this->quirksPositive);
    }

    public function GetRandomNegativeQuirk(){
        return $this->GetRandomData($this->quirksNegative);
    }

    public function GetRandomReward() {
        if (mt_rand(1, 100) <= self::rewardTrinketChance) {
            return $this->GetRandomData($this->trinkets);
        }
        return $this->GetRandomData($this->gold);
    }

    public function GetRandomRewardQuote() {
        return $this->GetRandomData($this->rewardsQuotes);
    }

    private function PopulateArray(&$array, $file_path, $isJson = false) {
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
