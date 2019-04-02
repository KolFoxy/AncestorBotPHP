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
 * Name: `file`
 *
 * This rule ensures a specific field is a (successful)  file upload. Usage: `file:FIELD_NAME`
 */
class File implements \CharlotteDunois\Validation\ValidationRule {
    /**
     * {@inheritdoc}
     * @return bool|string|array  Return false to "skip" the rule. Return true to mark the rule as passed.
     */
    function validate($value, $key, $fields, $options, $exists, \CharlotteDunois\Validation\Validator $validator) {
        if(!isset($_FILES[$key]) || !file_exists($_FILES[$key]['tmp_name']) || $_FILES[$key]['error'] != 0) {
            return 'formvalidator_make_invalid_file';
        }
        
        return true;
    }
}
