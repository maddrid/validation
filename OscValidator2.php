<?php

class OscValidator {

    protected  $validation_methods = array();
	protected $failedRules = array();
	protected $messages;
	protected $fallbackMessages = array();
	protected $customMessages = array();
	protected $data;
	protected $customValues = array();
	protected $files = array();
	protected $rules;
	protected $customAttributes = array();
	protected $after = array();
	protected $customMessages = array();
     public function __construct( array $data, array $rules, array $messages = array(), array $customAttributes = array())
    {

        $this->customMessages = $messages;
        $this->data = $this->parseData($data);
        $this->rules = $this->explodeRules($rules);
        $this->customAttributes = $customAttributes;
    }
	protected function parseData(array $data)
    {
        $this->files = array();

        foreach ($data as $key => $value) {

            if (in_array($value, $_FILES)) {
                $this->files[$key] = $value;

                unset($data[$key]);
            }
        }

        return $data;
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
        foreach ($rules as $key => &$rule) {
            $rule = (is_string($rule)) ? explode('|', $rule) : $rule;
        }

        return $rules;
    }
    public function hasValidator ($validator){
         return (isset($this->validation_methods [$validator])) ? TRUE : FALSE;

    }
     public function setValidator ($validator,$callback){
        return $this->validation_methods [$validator]= $callback;
    }

}