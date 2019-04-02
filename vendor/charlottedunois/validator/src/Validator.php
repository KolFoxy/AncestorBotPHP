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
 * The Validator.
 * Type Rules are non-exclusive (that means specifying two type rules means either one is passing).
 */
class Validator {
    protected $errors = array();
    protected $fields = array();
    protected $rules = array();
    
    /** @var string */
    protected $lang = null;
    
    /** @var \CharlotteDunois\Validation\LanguageInterface[] */
    protected static $languages = array();
    
    /** @var \CharlotteDunois\Validation\ValidationRule[] */
    protected static $rulesets = null;
    
    /** @var \CharlotteDunois\Validation\ValidationRule[] */
    protected static $langrules = array();
    
    /**
     * Constructor
     * @param  array   $fields
     * @param  array   $rules
     * @param  string  $lang
     */
    protected function __construct(array $fields, array $rules, string $lang) {
        $this->fields = $fields;
        $this->rules = $rules;
        $this->lang = $lang;
        
        if(static::$rulesets === null) {
            static::initRules();
        }
    }
    
    /**
     * Create a new Validator instance.
     * @param  array   $fields   The fields you wanna run the validation against.
     * @param  array   $rules    The validation rules.
     * @param  string  $lang     The language for error messages (included are 'en' or 'de').
     * @return Validator
     */
    static function make(array $fields, array $rules, string $lang = 'en') {
        return (new static($fields, $rules, $lang));
    }
    
    /**
     * Adds a new rule.
     * @param \CharlotteDunois\Validation\ValidationRule  $rule
     * @return void
     * @throws \InvalidArgumentException
     */
    static function addRule(\CharlotteDunois\Validation\ValidationRule $rule) {
        if(static::$rulesets === null) {
            static::initRules();
        }
        
        $class = get_class($rule);
        $arrname = explode('\\', $class);
        $name = array_pop($arrname);
        
        $rname = str_replace('rule', '', mb_strtolower($name));
        static::$rulesets[$rname] = $rule;
        
        if(mb_stripos($name, 'rule') !== false) {
            static::$langrules[] = $rname;
        }
    }
    
    /**
     * Adds a language to the languages array and makes it usable by the Validator.
     * @param string                                         $langCode
     * @param \CharlotteDunois\Validation\LanguageInterface  $language
     * @return void
     * @throws \InvalidArgumentException
     */
    static function addLanguage(string $langCode, \CharlotteDunois\Validation\LanguageInterface $language) {
        if(isset(static::$languages[$langCode])) {
            throw new \InvalidArgumentException('Given language code is already in use');
        }
        
        static::$languages[$langCode] = $language;
    }
    
    /**
     * Returns errors.
     * @return array
     */
    function errors() {
        return $this->errors;
    }
    
    /**
     * Determine if the data passes the validation rules.
     * @return bool
     * @throws \RuntimeException
     */
    function passes() {
        return $this->startValidation();
    }
    
    /**
     * Determine if the data fails the validation rules.
     * @return bool
     * @throws \RuntimeException
     */
    function fails() {
        return !($this->startValidation());
    }
    
    /**
     * Determines if the data passes the validation rules, or throws.
     * @param string  $class  Which exception class to throw.
     * @return bool
     * @throws \RuntimeException
     */
    function throw(string $class = '\RuntimeException') {
        return $this->startValidation($class);
    }
    
    /**
     * @return bool
     * @throws \RuntimeException
     */
    protected function startValidation(string $throws = '') {
        $vThrows = !empty($throws);
        
        foreach($this->rules as $key => $rule) {
            $set = explode('|', $rule);
            
            $exists = array_key_exists($key, $this->fields);
            $value = ($exists ? $this->fields[$key] : null);
            
            $passedLang = false;
            $failedOtherRules = false;
            
            $nullable = false;
            foreach($set as $r) {
                $r = explode(':', $r);
                if($r[0] === 'nullable') {
                    $nullable = true;
                    continue 1;
                } elseif(!isset(static::$rulesets[$r[0]])) {
                    throw new \RuntimeException('Validation Rule "'.$r[0].'" does not exist');
                }
                
                $return = static::$rulesets[$r[0]]->validate($value, $key, $this->fields, (array_key_exists(1, $r) ? $r[1] : null), $exists, $this);
                $passed = is_bool($return);
                
                if(in_array($r[0], static::$langrules)) {
                    if($passed) {
                        $passedLang = true;
                    } else {
                        if(!$passedLang) {
                            $passed = false;
                        }
                    }
                } else {
                    if(!$passed) {
                        $failedOtherRules = true;
                    }
                }
                
                if(!$passed) {
                    if(is_array($return)) {
                        $this->errors[$key] = $this->language($return[0], $return[1]);
                    } else {
                        $this->errors[$key] = $this->language($return);
                    }
                }
            }
            
            if($passedLang && !$failedOtherRules) {
                unset($this->errors[$key]);
            }
            
            if($exists && is_null($value)) {
                if(!$nullable) {
                    $this->errors[$key] = $this->language('formvalidator_make_nullable');
                } elseif($nullable && isset($this->errors[$key])) {
                    unset($this->errors[$key]);
                }
            }
            
            if($vThrows && !empty($this->errors[$key])) {
                throw new $throws($key.' '.lcfirst($this->errors[$key]));
            }
        }
        
        return empty($this->errors);
    }
    
    /**
     * Return the error message based on the language key (language based).
     *
     * @param  string  $key
     * @param  array   $replacements
     * @return string
     */
    function language(string $key, array $replacements = array()) {
        return static::$languages[$this->lang]->getTranslation($key, $replacements);
    }
    
    protected static function initRules() {
        static::$languages['en'] = new \CharlotteDunois\Validation\Languages\EnglishLanguage();
        static::$languages['de'] = new \CharlotteDunois\Validation\Languages\GermanLanguage();
        
        static::$rulesets = array();
        
        $rules = glob(__DIR__.'/Rules/*.php');
        foreach($rules as $rule) {
            $name = basename($rule, '.php');
            if($name === 'Nullable') {
                continue;
            }
            
            $class = '\\CharlotteDunois\\Validation\\Rules\\'.$name;
            static::addRule((new $class()));
        }
    }
}
