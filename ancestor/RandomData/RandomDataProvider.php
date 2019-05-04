<?php

namespace Ancestor\RandomData;

class RandomDataProvider {
    private $afflictions;
    private $virtues;
    private $NSFWquotes;
    private $gold;
    private $trinkets;
    private $rewardsQuotes;
    private $zalgoTitles;

    private static $instance = null;

    const virtueChance = 25;
    const rewardTrinketChance = 30;

    private function __construct() {
        $this->PopulateArray($this->rewardsQuotes, '/data/rewards/rewardsQuotes');
        $this->PopulateArray($this->gold, '/data/rewards/gold');
        $this->PopulateArray($this->trinkets, '/data/rewards/trinkets');
        $this->PopulateArray($this->zalgoTitles, '/data/zalgoTitles');
        $this->PopulateArray($this->virtues, '/data/virtues.json', true);
        $this->PopulateArray($this->NSFWquotes, '/data/NSFWquotes');
        $this->PopulateArray($this->afflictions, '/data/afflictions.json', true);
    }

    public static function GetInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new RandomDataProvider();
        }
        return self::$instance;
    }

    public function GetRandomZalgoCharacter() {
        return mb_chr(mt_rand(768, 879), 'UTF-8');
    }

    public function GetRandomZalgoString(int $size) {
        $rez = '';
        for ($i = 0; $i < $size; $i++) {
            $rez .= $this->GetRandomZalgoCharacter();
        }
        return $rez;
    }

    private function GetRandomData($array) {
        return $array[mt_rand(0, sizeof($array) - 1)];
    }

    private function GetRandomAffliction() {
        $affliction = $this->GetRandomData($this->afflictions['afflictions']);
        if (mt_rand(1, 100) <= 50) {
            $affliction['quote'] = $this->GetRandomData($this->afflictions['quotes']);
        }
        return $affliction;
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

    public function GetRandomZalgoTitle() {
        return $this->GetRandomData($this->zalgoTitles);
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
