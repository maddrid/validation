<<<<<<< HEAD
<?php

require 'OscFilter.php';
require 'OscValidator.php';
require 'OscValidationError.php';
require 'OscValidationRule.php';
class OscValidation {

    protected $fieldCharsToRemove = array('_', '-');
    // Validation rules for execution
    protected $validation_rules = array();
    // Filter rules for execution
    protected $filter_rules = array();
    // Instance attribute containing errors from last run
    protected $errors = array();
    private $fields = array();
    private $filterclass;
    private $ruleclass;
    private $validatorclass;

    function __construct() {

        $this->reload();
    }

    //Singleton instance of GUMP
    protected static $instance = null;

    public static function newInstance() {
        if (self::$instance === null) {
            self::$instance = new self;
            //  self::$instance->reload();
        }
        return self::$instance;
    }

    /**
     * Load latest fields
     *
     */
    public function reload() {
        $this->ruleclass = OscValidationRule::newInstance();
        $this->filterclass = OscFilter::newInstance();
        $this->validatorclass = OscValidator::newInstance();
        $this->fields = PluginFields::newInstance()->getFields();
    }

    /**
     * Shorthand method for running only the data filters.
     *
     * @param array $data
     * @param array $filters
     *
     * @return mixed
     */
    public function filter(array $data, $filters = null) {
        if ($filters == null) {
             throw new Exception(" Null filters.");
        }
        return $this->filterclass->filter($data, $filters);
    }

    public function validate(array $data, $rules = null) {
        if ($rules == null) {
            throw new Exception(" Null validation rules.");
        }

        $valid = $this->validatorclass->validate($data, $rules);
        return $valid ;
    }

    /**
     * Adds a custom validation rule using a callback function.
     *
     * @param string   $rule
     * @param callable $callback
     *
     * @return bool
     *
     * @throws Exception
     */
    public function add_validator($rule, $callback) {
        $method = 'validate_' . $rule;

        if (method_exists($this->validatorclass, $method) || ($this->validatorclass->hasValidator($rule))) {
            throw new Exception("Validator rule '$rule' already exists.");
        }

        $this->validatorclass->setValidator($rule, $callback);

        return true;
    }

    /**
     * Adds a custom filter using a callback function.
     *
     * @param string   $rule
     * @param callable $callback
     *
     * @return bool
     *
     * @throws Exception
     */
    public function add_filter($rule, $callback) {
        $method = 'filter_' . $rule;

        if (method_exists($this->filterclass, $method) || ($this->filterclass->hasFilter($rule))) {
            throw new Exception("Filter rule '$rule' already exists.");
        }

        $this->filterclass->setFilter($rule, $callback);

        return true;
    }

    public function getValidators() {
        return  $this->validatorclass->getValidators();
    }

    public function getFilters() {
        return $this->filterclass->getFilters();
    }



}
=======
<?php

require 'OscFilter.php';
require 'OscValidator.php';
require 'OscValidationError.php';
require 'OscValidationRule.php';
class OscValidation {

    protected $fieldCharsToRemove = array('_', '-');
    // Validation rules for execution
    protected $validation_rules = array();
    // Filter rules for execution
    protected $filter_rules = array();
    // Instance attribute containing errors from last run
    protected $errors = array();
    private $fields = array();
    private $filterclass;
    private $ruleclass;
    private $validatorclass;

    function __construct() {

        $this->reload();
    }

    //Singleton instance of GUMP
    protected static $instance = null;

    public static function newInstance() {
        if (self::$instance === null) {
            self::$instance = new self;
            //  self::$instance->reload();
        }
        return self::$instance;
    }

    /**
     * Load latest fields
     *
     */
    public function reload() {
        $this->ruleclass = OscValidationRule::newInstance();
        $this->filterclass = OscFilter::newInstance();
        $this->validatorclass = OscValidator::newInstance();
        $this->fields = PluginFields::newInstance()->getFields();
    }

    /**
     * Shorthand method for running only the data filters.
     *
     * @param array $data
     * @param array $filters
     *
     * @return mixed
     */
    public function filter(array $data, $filters = null) {
        if ($filters == null) {
             throw new Exception(" Null filters.");
        }
        return $this->filterclass->filter($data, $filters);
    }

    public function validate(array $data, $rules = null) {
        if ($rules == null) {
            throw new Exception(" Null validation rules.");
        }

        $valid = $this->validatorclass->validate($data, $rules);
        return $valid ;
    }

    /**
     * Adds a custom validation rule using a callback function.
     *
     * @param string   $rule
     * @param callable $callback
     *
     * @return bool
     *
     * @throws Exception
     */
    public function add_validator($rule, $callback) {
        $method = 'validate_' . $rule;

        if (method_exists($this->validatorclass, $method) || ($this->validatorclass->hasValidator($rule))) {
            throw new Exception("Validator rule '$rule' already exists.");
        }

        $this->validatorclass->setValidator($rule, $callback);

        return true;
    }

    /**
     * Adds a custom filter using a callback function.
     *
     * @param string   $rule
     * @param callable $callback
     *
     * @return bool
     *
     * @throws Exception
     */
    public function add_filter($rule, $callback) {
        $method = 'filter_' . $rule;

        if (method_exists($this->filterclass, $method) || ($this->filterclass->hasFilter($rule))) {
            throw new Exception("Filter rule '$rule' already exists.");
        }

        $this->filterclass->setFilter($rule, $callback);

        return true;
    }

    public function getValidators() {
        return  $this->validatorclass->getValidators();
    }

    public function getFilters() {
        return $this->filterclass->getFilters();
    }



}
>>>>>>> 6bf0f5ec47c8db86e07fe722fe1d84edbce93a52
