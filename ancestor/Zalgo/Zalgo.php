<?php

namespace Ancestor\Zalgo;

class Zalgo {
    const UTF8_ZALGO_FIRST = 768;
    const UTF8_ZALGO_LAST = 879;

    public static function zalgorizeString(string $input, int $zalgoPerChar) {
        $result = '';
        $strLen = mb_strlen($input);
        for ($i = 0; $i < $strLen; $i++) {
            $result .= self::getRandomZalgoString($zalgoPerChar) . mb_substr($input, $i, 1);
        }
        return $result;
    }

    public static function getRandomZalgoChar(): string {
        return mb_chr(mt_rand(self::UTF8_ZALGO_FIRST, self::UTF8_ZALGO_LAST), 'UTF-8');
    }

    public static function getRandomZalgoString(int $size): string {
        $rez = '';
        for ($i = 0; $i < $size; $i++) {
            $rez .= self::getRandomZalgoChar();
        }
        return $rez;
    }

}