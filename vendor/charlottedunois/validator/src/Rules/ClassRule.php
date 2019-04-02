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
 * Name: `class` - Type Rule
 *
 * This rule ensures a specific field is a string containing a valid class name or a class instance.
 * The options value ensures the class is either of that type, or extending it or implementing it.
 *
 * You can ensure that only class names get passed by appending `,string_only` - or only objects by `,object_only`.
 *
 * Usage: `class` or `class:CLASS_NAME`
 */
class ClassRule implements \CharlotteDunois\Validation\ValidationRule {
    /**
     * {@inheritdoc}
     * @return bool|string|array  Return false to "skip" the rule. Return true to mark the rule as passed.
     */
    function validate($value, $key, $fields, $options, $exists, \CharlotteDunois\Validation\Validator $validator) {
        if(!$exists) {
            return false;
        }
        
        $is_string = is_string($value);
        $is_object = is_object($value);
        
        if(!$is_string && !$is_object) {
            return 'formvalidator_make_class';
        }
        
        $options = explode(',', $options);
        $class = ltrim($options[0], '\\');
        
        if(!empty($options[1]) && $options[1] === 'string_only' && !$is_string) {
            return 'formvalidator_make_class_stringonly';
        }
        
        if(!empty($options[1]) && $options[1] === 'object_only' && !$is_object) {
            return 'formvalidator_make_class_objectonly';
        }
        
        if($is_string && !class_exists($value)) {
            return 'formvalidator_make_class';
        }
        
        if(!is_a($value, $class, true) && !in_array($class, class_parents($value)) && !in_array($class, class_implements($value))) {
            return array('formvalidator_make_class_inheritance', array('{0}' => $class));
        }
        
        return true;
    }
}
