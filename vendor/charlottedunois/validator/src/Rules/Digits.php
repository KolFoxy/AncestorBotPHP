<?php
/**
 * Validator
 * Copyright 2017-2018 Charlotte Dunois, All Rights Reserved
 *
 * Website: https://charuru.moe
 * License: https://github.com/CharlotteDunois/Validator/blob/master/LICENSE
**/

namespace CharlotteDunois\Validation\Rules;

/**
 * Name: `digits`
 *
 * This rule ensures a specific field is a numeric value (string) with the specified length. Usage: `digits:LENGTH`
 */
class Digits implements \CharlotteDunois\Validation\ValidationRule {
    /**
     * {@inheritdoc}
     * @return bool|string|array  Return false to "skip" the rule. Return true to mark the rule as passed.
     */
    function validate($value, $key, $fields, $options, $exists, \CharlotteDunois\Validation\Validator $validator) {
        if(!$exists) {
            return false;
        }
        
        if(!is_numeric($value) || mb_strlen(((string) $value)) != $options) {
            return array('formvalidator_make_digits', array('{0}' => $options));
        }
        
        return true;
    }
}
