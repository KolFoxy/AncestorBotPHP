<?php

namespace Ancestor\RandomData;

class RandomDataProvider {
    private static $afflictions = array('Paranoid',
        'Selfish',
        'Irrational',
        'Fearful',
        'Hopeless',
        'Abusive',
        'Masochistic');
    private static $virtues = array('Powerful',
        'Courageous',
        'Stalwart',
        'Vigorous',
        'Focused',
        'Heroic');
    private static $NSFWquotes;
    private static $gold;
    private static $trinkets;
    private static $rewardsQuotes;
    private static $zalgoTitles;

    const virtueChance = 25;
    const rewardTrinketChance = 30;

    public static function getRandomZalgoCharacter() {
        return html_entity_decode('&#x' . sprintf('%01x', mt_rand(768, 879)) . ';');
    }

    public static function getRandomZalgoString(int $size) {
        $rez='';
        for ($i = 0; $i < $size; $i++) {
            $rez.= self::getRandomZalgoCharacter();
        }
        return $rez;
    }

    private static function getRandomData($array) {
        return $array[mt_rand(0, sizeof($array) - 1)];
    }

    public static function GetRandomResolve() {
        if (mt_rand(1, 100) <= self::virtueChance) {
            return self::getRandomData(self::$virtues);
        }
        return self::getRandomData(self::$afflictions);
    }

    public static function GetRandomNSFWQuote() {
        self::CheckUpdateArray(self::$NSFWquotes, '/data/NSFWquotes');
        return self::getRandomData(self::$NSFWquotes);
    }
    public static function GetRandomZalgoTitle() {
        self::CheckUpdateArray(self::$zalgoTitles, '/data/zalgoTitles');
        return self::getRandomData(self::$zalgoTitles);
    }

    public static function GetRandomReward() {
        self::CheckUpdateArray(self::$gold, '/data/rewards/gold');
        self::CheckUpdateArray(self::$trinkets, '/data/rewards/trinkets');
        if (mt_rand(1, 100) <= self::rewardTrinketChance) {
            return self::getRandomData(self::$trinkets);
        }
        return self::getRandomData(self::$gold);
    }

    public static function GetRandomRewardQuote() {
        self::CheckUpdateArray(self::$rewardsQuotes, '/data/rewards/rewardsQuotes');
        return self::getRandomData(self::$rewardsQuotes);
    }

    private static function CheckUpdateArray(&$array, $file_path) {
        if (empty($array)) $array = file(dirname(__DIR__, 2) . $file_path);
    }
}
