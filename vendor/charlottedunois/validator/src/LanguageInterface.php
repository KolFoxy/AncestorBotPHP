<?php
/**
 * Validator
 * Copyright 2017-2018 Charlotte Dunois, All Rights Reserved
 *
 * Website: https://charuru.moe
 * License: https://github.com/CharlotteDunois/Validator/blob/master/LICENSE
**/

namespace CharlotteDunois\Validation;

/**
 * The language interface defines a strict way to get a language translation for a string (denoted by key).
 */
interface LanguageInterface {
    /**
     * Get a translation string, denoted by key. Replace the `$replacements` keys by their values in that string.
     * @param string  $key
     * @param array   $replacements
     * @return string
     */
    function getTranslation(string $key, array $replacements = array());
}
