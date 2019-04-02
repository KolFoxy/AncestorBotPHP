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
 * Name: `max`
 *
 * This rule ensures a specific field is/has:
 *   - numeric: equal/smaller than the specified value
 *   - file: filesize is equal/smaller than the specified value (in kibibytes)
 *   - array: equal/less elements than specified value
 *   - string: equal/less characters than specified value
 *
 * Usage: `max:VALUE`
 */
class Max implements \CharlotteDunois\Validation\ValidationRule {
    /**
     * {@inheritdoc}
     * @return bool|string|array  Return false to "skip" the rule. Return true to mark the rule as passed.
     */
    function validate($value, $key, $fields, $options, $exists, \CharlotteDunois\Validation\Validator $validator) {
        if(isset($_FILES[$key]) && file_exists($_FILES[$key]['tmp_name']) && $_FILES[$key]['error'] == 0) {
            $v = round((filesize($_FILES[$key]['tmp_name']) / 1024));
        } else {
            if(!$exists) {
                return false;
            }
            
            if(is_array($value)) {
                $v = count($value);
            } elseif(is_numeric($value)) {
                $v = $value;
            } else {
                $v = mb_strlen($value);
            }
        }
        
        if($v > $options) {
            if(is_string($value)) {
                return array('formvalidator_make_max_string', array('{0}' => $options));
            } else {
                return array('formvalidator_make_max', array('{0}' => $options));
            }
            
        }
        
        return true;
    }
}
