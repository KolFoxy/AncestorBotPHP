<?php

namespace Ancestor\RandomData;

class RandomDataProvider {
    private static $afflictions;
    private static $virtues;
    private static $NSFWquotes;
    private static $gold;
    private static $trinkets;
    private static $rewardsQuotes;
    private static $zalgoTitles;

    const virtueChance = 25;
    const rewardTrinketChance = 30;

    public static function GetRandomZalgoCharacter() {
        return html_entity_decode('&#x' . sprintf('%01x', mt_rand(768, 879)) . ';');
    }

    public static function GetRandomZalgoString(int $size) {
        $rez = '';
        for ($i = 0; $i < $size; $i++) {
            $rez .= self::GetRandomZalgoCharacter();
        }
        return $rez;
    }

    private static function GetRandomData($array) {
        return $array[mt_rand(0, sizeof($array) - 1)];
    }

    private static function GetRandomAffliction() {
        $affliction = self::GetRandomData(self::$afflictions['afflictions']);
        if (mt_rand(1,100)<=50){
            $affliction['quote'] = self::GetRandomData(self::$afflictions['quotes']);
        }
        return $affliction;
    }

    public static function GetRandomResolve() {
        if (mt_rand(1, 100) <= self::virtueChance) {
            self::CheckUpdateArray(self::$virtues, '/data/virtues.json',true);
            return self::GetRandomData(self::$virtues['virtues']);
        }
        self::CheckUpdateArray(self::$afflictions, '/data/afflictions.json',true);
        return self::GetRandomAffliction();
    }

    public static function GetRandomNSFWQuote() {
        self::CheckUpdateArray(self::$NSFWquotes, '/data/NSFWquotes');
        return self::GetRandomData(self::$NSFWquotes);
    }

    public static function GetRandomZalgoTitle() {
        self::CheckUpdateArray(self::$zalgoTitles, '/data/zalgoTitles');
        return self::GetRandomData(self::$zalgoTitles);
    }

    public static function GetRandomReward() {
        self::CheckUpdateArray(self::$gold, '/data/rewards/gold');
        self::CheckUpdateArray(self::$trinkets, '/data/rewards/trinkets');
        if (mt_rand(1, 100) <= self::rewardTrinketChance) {
            return self::GetRandomData(self::$trinkets);
        }
        return self::GetRandomData(self::$gold);
    }

    public static function GetRandomRewardQuote() {
        self::CheckUpdateArray(self::$rewardsQuotes, '/data/rewards/rewardsQuotes');
        return self::GetRandomData(self::$rewardsQuotes);
    }

    private static function CheckUpdateArray(&$array, $file_path, $isJson = false) {
        if (!empty($array)) {
            return;
        }
        if (!$isJson) {
            $array = file(dirname(__DIR__, 2) . $file_path);
            return;
        }
        $array = json_decode(file_get_contents(dirname(__DIR__, 2) . $file_path),true);
    }
}
