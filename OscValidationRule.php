<?php

class OscValidationRule {

    // Validation rules for execution
    private $rules = array();
    // Validation rules for execution
    private $privateRules = array();
    // Filter rules for execution
    private $filter_rules = array();
    //  The failed validation rules.
    private $failedRules = array();

     private static $instance;

    /**
     * It creates a new Field object class ir if it has been created
     * before, it return the previous object
     *
     * @access public
     * @since unknown
     * @return Field
     */
    public static function newInstance() {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }
        return self::$instance;
    }
     public function getValidationRules() {
        return $this->rules;
    }

    public function getFilters() {
        return $this->filter_rules;
    }
     protected function getRules($rules)
    {
        $return = array();
        if (!is_array($rules)) {
            return $rules;
        }
        foreach ($rules as $rule) {
            $array = explode('|', $rule);

            foreach ($array as $filter) {
                $return[] = $filter;
            }
        }
        return implode('|', array_unique($return));
    }
    public function drawRules($rules){
        $new = $this->explodeRules(array($rules));
        return $new;
    }
     /**
     * Merge additional rules into a given attribute.
     *
     * @param  string  $field
     * @param  string|array  $rules
     *
     * @return void
     */
    public function mergeRules($field, $rules)
    {
        $current = isset($this->rules[$field]) ? $this->rules[$field] :array();

        //$merge = reset($this->explodeRules(array(&$rules)));

        $this->rules[$field] = array_merge($current, $rules);
    }


     /**
     * Define a set of rules that apply to each element in an array attribute.
     *
     * @param  string  $field
     * @param  string|array  $rules
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function each($field, $rules)
    {
        $data = array_get($this->fields, $field);

        if (!is_array($data)) {
            if ($this->hasRule($field, 'Array'))
                return;

            throw new \InvalidArgumentException('Attribute for each() must be an array.');
        }

        foreach ($data as $dataKey => $dataValue) {
            foreach ($rules as $ruleValue) {
                $this->mergeRules("$field.$dataKey", $ruleValue);
            }
        }
    }

    /**
     * Explode the rules into an array of rules.
     *
     * @param  string|array  $rules
     *
     * @return array
     */
    protected function explodeRules($rules)
    {
        $new_rules = array();
        foreach ($rules as $key => $rule) {
            $new_rules[] = (is_string($rule)) ? explode('|', $rule) : $rule;
        }

        return $new_rules;
    }

    /**
     * Add a failed rule and error message to the collection.
     *
     * @param  string  $field
     * @param  string  $rule
     * @param  array   $parameters
     *
     * @return void
     */
    protected function isPrivate($rule)
    {


        return in_array($rule, $this->privateRules);
    }


    /**
     * Add a failed rule and error message to the collection.
     *
     * @param  string  $field
     * @param  string  $rule
     * @param  array   $parameters
     *
     * @return void
     */
    protected function addFailure($field, $rule, $parameters)
    {
        $this->addError($field, $rule, $parameters);

        $this->failedRules[$field][$rule] = $parameters;
    }

    /**
     * Add an error message to the validator's collection of messages.
     *
     * @param  string  $field
     * @param  string  $rule
     * @param  array   $parameters
     *
     * @return void
     */
    protected function addError($field, $rule, $parameters)
    {
        $message = $this->getMessage($field, $rule);

        $message = $this->doReplacements($message, $field, $rule, $parameters);

        if ($this->isPrivate($rule)) {
            $this->messages->add($field, $message, true);
        } else {

            $this->messages->add($field, $message);
        }
    }



     /**
     * Determine if the given attribute has a rule in the given set.
     *
     * @param  string  $field
     * @param  string|array  $rules
     *
     * @return bool
     */
    protected function hasRule($field, $rules)
    {
        return !is_null($this->getRule($field, $rules));
    }

    /**
     * Get a rule and its parameters for a given attribute.
     *
     * @param  string  $field
     * @param  string|array  $rules
     *
     * @return array|null
     */
    protected function getRule($field, $rules)
    {
        if (!array_key_exists($field, $this->rules)) {
            return;
        }

        $rules = (array) $rules;

        foreach ($this->rules[$field] as $rule) {
            list($rule, $parameters) = $this->parseRule($rule);

            if (in_array($rule, $rules))
                return [$rule, $parameters];
        }
    }

    /**
     * Extract the rule name and parameters from a rule.
     *
     * @param  array|string  $rules
     *
     * @return array
     */
    public function parseRule($rules)
    {
        if (is_array($rules)) {
            return $this->parseArrayRule($rules);
        }

        return $this->parseStringRule($rules);
    }

    /**
     * Parse an array based rule.
     *
     * @param  array  $rules
     *
     * @return array
     */
    protected function parseArrayRule(array $rules)
    {
        return array(studly_case(trim(array_get($rules, 0))), array_slice($rules, 1));
    }

    /**
     * Parse a string based rule.
     *
     * @param  string  $rules
     *
     * @return array
     */
    protected function parseStringRule($rules)
    {
        $parameters = array();

        // The format for specifying validation rules and parameters follows an
        // easy {rule}:{parameters} formatting convention. For instance the
        // rule "Max:3" states that the value may only be three letters.
        if (strpos($rules, ':') !== false) {
            list($rules, $parameter) = explode(':', $rules, 2);

            $parameters = $this->parseParameters($rules, $parameter);
        }

        return array(studly_case(trim($rules)), $parameters);
    }

    /**
     * Parse a parameter list.
     *
     * @param  string  $rule
     * @param  string  $parameter
     *
     * @return array
     */
    protected function parseParameters($rule, $parameter)
    {
        if (strtolower($rule) == 'regex')
            return array($parameter);

        return str_getcsv($parameter);
    }

}

