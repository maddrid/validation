<?php

class OscValidator {


	private $errorclass;
    protected $validation_methods = array();

    /**
     * It references to self object: Field.
     * It is used as a singleton
     *
     * @access private
     * @since unknown
     * @var Field
     */
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

    function __construct() {
         $this->errorclass = OscValidationError::newInstance();
    }

    public function hasValidator($validator) {
        return (isset($this->validation_methods [$validator])) ? TRUE : FALSE;
    }

    public function setValidator($validator, $callback) {
        return $this->validation_methods [$validator] = $callback;
    }

    public function getValidators() {
        return $this->validation_methods;
    }

    /**
     * Perform data validation against the provided ruleset.
     *
     * @param mixed $input
     * @param array $ruleset
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function validate(array $input, array $ruleset) {


        foreach ($ruleset as $field => $rules) {

            $rules = explode('|', $rules);

            $lookFor = array('required_file', 'required');

            if (count(array_intersect($lookFor, $rules)) > 0 || (isset($input[$field]) && !is_array($input[$field]))) {

                if (is_array($input[$field])) {
                    $input_array = $input[$field];
                } else {
                    $input_array = array($input[$field]);
                }

                foreach ($input_array as $value) {

                    $input[$field] = $value;

                    foreach ($rules as $rule) {
                        $method = null;
                        $param = null;

                        // Check if we have rule parameters
                        if (strstr($rule, ',') !== false) {
                            $rule = explode(',', $rule);
                            $method = 'validate_' . $rule[0];
                            $param = $rule[1];
                            $rule = $rule[0];

                            // If there is a reference to a field
                            if (preg_match('/(?:(?:^|;)_([a-z_]+))/', $param, $matches)) {

                                // If provided parameter is a field
                                if (isset($input[$matches[1]])) {
                                    $param = str_replace('_' . $matches[1], $input[$matches[1]], $param);
                                }
                            }
                        } else {
                            $method = 'validate_' . $rule;
                        }

                        //self::$validation_methods[$rule] = $callback;

                        if (is_callable(array($this, $method))) {
                            $result = $this->$method(
                                    $field, $input, $param
                            );

                            if (is_array($result)) {

                                    $this->errorclass->addError( $field,$result);
                                
                            }
                        } elseif (isset($this->validation_methods[$rule])) {
                            $result = call_user_func($this->validation_methods[$rule], $field, $input, $param);

                            if ($result === false) {
                                if (count(array_column($this->errors, 'field')) === 0) {
                                   $this->errorclass->addError($field, array(
                                        'field' => $field,
                                        'value' => $input,
                                        'rule' => $rule,
                                        'param' => $param,
                                    ));
                                }
                            }
                        } else {
                            throw new Exception("Validator method '$method' does not exist.");
                        }
                    }
                }
            }
        }
$errors = $this->errorclass->getErrors();
        return (count($errors) > 0) ?false : true;
    }

    /**
     * Verify that a value is contained within the pre-defined value set.
     *
     * Usage: '<index>' => 'contains,value value value'
     *
     * @param string $field
     * @param array  $input
     * @param null   $param
     *
     * @return mixed
     */
    protected function validate_contains($field, $input, $param = null) {
        if (!isset($input[$field])) {
            return;
        }

        $param = trim(strtolower($param));

        $value = trim(strtolower($input[$field]));

        if (preg_match_all('#\'(.+?)\'#', $param, $matches, PREG_PATTERN_ORDER)) {
            $param = $matches[1];
        } else {
            $param = explode(chr(32), $param);
        }

        if (in_array($value, $param)) { // valid, return nothing
            return;
        }

        return array(
            'field' => $field,
            'value' => $value,
            'rule' => __FUNCTION__,
            'param' => $param,
        );
    }

    /**
     * Verify that a value is contained within the pre-defined value set.
     * OUTPUT: will NOT show the list of values.
     *
     * Usage: '<index>' => 'contains_list,value;value;value'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_contains_list($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        $param = trim(strtolower($param));

        $value = trim(strtolower($input[$field]));

        $param = explode(';', $param);

        // consider: in_array(strtolower($value), array_map('strtolower', $param)

        if (in_array($value, $param)) { // valid, return nothing
            return;
        } else {
            return array(
                'field' => $field,
                'value' => $value,
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Verify that a value is NOT contained within the pre-defined value set.
     * OUTPUT: will NOT show the list of values.
     *
     * Usage: '<index>' => 'doesnt_contain_list,value;value;value'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_doesnt_contain_list($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        $param = trim(strtolower($param));

        $value = trim(strtolower($input[$field]));

        $param = explode(';', $param);

        if (!in_array($value, $param)) { // valid, return nothing
            return;
        } else {
            return array(
                'field' => $field,
                'value' => $value,
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Check if the specified key is present and not empty.
     *
     * Usage: '<index>' => 'required'
     *
     * @param string $field
     * @param array  $input
     * @param null   $param
     *
     * @return mixed
     */
    protected function validate_required($field, $input, $param = null) {
        if (isset($input[$field]) && ($input[$field] === false || $input[$field] === 0 || $input[$field] === 0.0 || $input[$field] === '0' || !empty($input[$field]))) {
            return;
        }

        return array(
            'field' => $field,
            'value' => null,
            'rule' => __FUNCTION__,
            'param' => $param,
        );
    }

    /**
     * Determine if the provided email is valid.
     *
     * Usage: '<index>' => 'valid_email'
     *
     * @param string $field
     * @param array  $input
     * @param null   $param
     *
     * @return mixed
     */
    protected function validate_valid_email($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (!filter_var($input[$field], FILTER_VALIDATE_EMAIL)) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Determine if the provided value length is less or equal to a specific value.
     *
     * Usage: '<index>' => 'max_len,240'
     *
     * @param string $field
     * @param array  $input
     * @param null   $param
     *
     * @return mixed
     */
    protected function validate_max_len($field, $input, $param = null) {
        if (!isset($input[$field])) {
            return;
        }

        if (function_exists('mb_strlen')) {
            if (mb_strlen($input[$field]) <= (int) $param) {
                return;
            }
        } else {
            if (strlen($input[$field]) <= (int) $param) {
                return;
            }
        }

        return array(
            'field' => $field,
            'value' => $input[$field],
            'rule' => __FUNCTION__,
            'param' => $param,
        );
    }

    /**
     * Determine if the provided value length is more or equal to a specific value.
     *
     * Usage: '<index>' => 'min_len,4'
     *
     * @param string $field
     * @param array  $input
     * @param null   $param
     *
     * @return mixed
     */
    protected function validate_min_len($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (function_exists('mb_strlen')) {
            if (mb_strlen($input[$field]) >= (int) $param) {
                return;
            }
        } else {
            if (strlen($input[$field]) >= (int) $param) {
                return;
            }
        }

        return array(
            'field' => $field,
            'value' => $input[$field],
            'rule' => __FUNCTION__,
            'param' => $param,
        );
    }

    /**
     * Determine if the provided value length matches a specific value.
     *
     * Usage: '<index>' => 'exact_len,5'
     *
     * @param string $field
     * @param array  $input
     * @param null   $param
     *
     * @return mixed
     */
    protected function validate_exact_len($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (function_exists('mb_strlen')) {
            if (mb_strlen($input[$field]) == (int) $param) {
                return;
            }
        } else {
            if (strlen($input[$field]) == (int) $param) {
                return;
            }
        }

        return array(
            'field' => $field,
            'value' => $input[$field],
            'rule' => __FUNCTION__,
            'param' => $param,
        );
    }

    /**
     * Determine if the provided value contains only alpha characters.
     *
     * Usage: '<index>' => 'alpha'
     *
     * @param string $field
     * @param array  $input
     * @param null   $param
     *
     * @return mixed
     */
    protected function validate_alpha($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (!preg_match('/^([a-zÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ])+$/i', $input[$field]) !== false) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Determine if the provided value contains only alpha-numeric characters.
     *
     * Usage: '<index>' => 'alpha_numeric'
     *
     * @param string $field
     * @param array  $input
     * @param null   $param
     *
     * @return mixed
     */
    protected function validate_alpha_numeric($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (!preg_match('/^([a-z0-9ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ])+$/i', $input[$field]) !== false) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Determine if the provided value contains only alpha characters with dashed and underscores.
     *
     * Usage: '<index>' => 'alpha_dash'
     *
     * @param string $field
     * @param array  $input
     * @param null   $param
     *
     * @return mixed
     */
    protected function validate_alpha_dash($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (!preg_match('/^([a-z0-9ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ_-])+$/i', $input[$field]) !== false) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Determine if the provided value contains only alpha numeric characters with spaces.
     *
     * Usage: '<index>' => 'alpha_numeric_space'
     *
     * @param string $field
     * @param array  $input
     * @param null   $param
     *
     * @return mixed
     */
    protected function validate_alpha_numeric_space($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (!preg_match("/^([a-z0-9ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\s])+$/i", $input[$field]) !== false) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Determine if the provided value contains only alpha numeric characters with spaces.
     *
     * Usage: '<index>' => 'alpha_space'
     *
     * @param string $field
     * @param array  $input
     * @param null   $param
     *
     * @return mixed
     */
    protected function validate_alpha_space($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (!preg_match("/^([0-9a-zÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\s])+$/i", $input[$field]) !== false) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Determine if the provided value contains only alpha numeric characters with spaces.
     *
     * Usage: '<index>' => 'alpha_space'
     *
     * @param string $field
     * @param array  $input
     * @param null   $param
     *
     * @return mixed
     */
    protected function validate_array($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (!is_array($input[$field])) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }
    /**
     * Determine if the provided value is a valid number or numeric string.
     *
     * Usage: '<index>' => 'numeric'
     *
     * @param string $field
     * @param array  $input
     * @param null   $param
     *
     * @return mixed
     */
    protected function validate_numeric($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (!is_numeric($input[$field])) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Determine if the provided value is a valid integer.
     *
     * Usage: '<index>' => 'integer'
     *
     * @param string $field
     * @param array  $input
     * @param null   $param
     *
     * @return mixed
     */
    protected function validate_integer($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (filter_var($input[$field], FILTER_VALIDATE_INT) === false) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Determine if the provided value is a PHP accepted boolean.
     *
     * Usage: '<index>' => 'boolean'
     *
     * @param string $field
     * @param array  $input
     * @param null   $param
     *
     * @return mixed
     */
    protected function validate_boolean($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field]) && $input[$field] !== 0) {
            return;
        }

        $booleans = array('1', 'true', true, 1, '0', 'false', false, 0, 'yes', 'no', 'on', 'off');
        if (in_array($input[$field], $booleans, true)) {
            return;
        }

        return array(
            'field' => $field,
            'value' => $input[$field],
            'rule' => __FUNCTION__,
            'param' => $param,
        );
    }

    /**
     * Determine if the provided value is a valid float.
     *
     * Usage: '<index>' => 'float'
     *
     * @param string $field
     * @param array  $input
     * @param null   $param
     *
     * @return mixed
     */
    protected function validate_float($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (filter_var($input[$field], FILTER_VALIDATE_FLOAT) === false) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Determine if the provided value is a valid URL.
     *
     * Usage: '<index>' => 'valid_url'
     *
     * @param string $field
     * @param array  $input
     * @param null   $param
     *
     * @return mixed
     */
    protected function validate_valid_url($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (!filter_var($input[$field], FILTER_VALIDATE_URL)) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Determine if a URL exists & is accessible.
     *
     * Usage: '<index>' => 'url_exists'
     *
     * @param string $field
     * @param array  $input
     * @param null   $param
     *
     * @return mixed
     */
    protected function validate_url_exists($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        $url = parse_url(strtolower($input[$field]));

        if (isset($url['host'])) {
            $url = $url['host'];
        }

        if (function_exists('checkdnsrr')) {
            if (checkdnsrr($url) === false) {
                return array(
                    'field' => $field,
                    'value' => $input[$field],
                    'rule' => __FUNCTION__,
                    'param' => $param,
                );
            }
        } else {
            if (gethostbyname($url) == $url) {
                return array(
                    'field' => $field,
                    'value' => $input[$field],
                    'rule' => __FUNCTION__,
                    'param' => $param,
                );
            }
        }
    }

    /**
     * Determine if the provided value is a valid IP address.
     *
     * Usage: '<index>' => 'valid_ip'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_valid_ip($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (!filter_var($input[$field], FILTER_VALIDATE_IP) !== false) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Determine if the provided value is a valid IPv4 address.
     *
     * Usage: '<index>' => 'valid_ipv4'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     *
     * @see http://pastebin.com/UvUPPYK0
     */
    /*
     * What about private networks? http://en.wikipedia.org/wiki/Private_network
     * What about loop-back address? 127.0.0.1
     */
    protected function validate_valid_ipv4($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (!filter_var($input[$field], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // removed !== FALSE

            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Determine if the provided value is a valid IPv6 address.
     *
     * Usage: '<index>' => 'valid_ipv6'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_valid_ipv6($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (!filter_var($input[$field], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Determine if the input is a valid credit card number.
     *
     * See: http://stackoverflow.com/questions/174730/what-is-the-best-way-to-validate-a-credit-card-in-php
     * Usage: '<index>' => 'valid_cc'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_valid_cc($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        $number = preg_replace('/\D/', '', $input[$field]);

        if (function_exists('mb_strlen')) {
            $number_length = mb_strlen($number);
        } else {
            $number_length = strlen($number);
        }

        $parity = $number_length % 2;

        $total = 0;

        for ($i = 0; $i < $number_length; ++$i) {
            $digit = $number[$i];

            if ($i % 2 == $parity) {
                $digit *= 2;

                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $total += $digit;
        }

        if ($total % 10 == 0) {
            return; // Valid
        }

        return array(
            'field' => $field,
            'value' => $input[$field],
            'rule' => __FUNCTION__,
            'param' => $param,
        );
    }

    /**
     * Determine if the input is a valid human name [Credits to http://github.com/ben-s].
     *
     * See: https://github.com/Wixel/GUMP/issues/5
     * Usage: '<index>' => 'valid_name'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_valid_name($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (!preg_match("/^([a-zÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝàáâãäåçèéêëìíîïñðòóôõöùúûüýÿ '-])+$/i", $input[$field]) !== false) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Determine if the provided input is likely to be a street address using weak detection.
     *
     * Usage: '<index>' => 'street_address'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_street_address($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        // Theory: 1 number, 1 or more spaces, 1 or more words
        $hasLetter = preg_match('/[a-zA-Z]/', $input[$field]);
        $hasDigit = preg_match('/\d/', $input[$field]);
        $hasSpace = preg_match('/\s/', $input[$field]);

        $passes = $hasLetter && $hasDigit && $hasSpace;

        if (!$passes) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Determine if the provided value is a valid IBAN.
     *
     * Usage: '<index>' => 'iban'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_iban($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        static $character = array(
            'A' => 10, 'C' => 12, 'D' => 13, 'E' => 14, 'F' => 15, 'G' => 16,
            'H' => 17, 'I' => 18, 'J' => 19, 'K' => 20, 'L' => 21, 'M' => 22,
            'N' => 23, 'O' => 24, 'P' => 25, 'Q' => 26, 'R' => 27, 'S' => 28,
            'T' => 29, 'U' => 30, 'V' => 31, 'W' => 32, 'X' => 33, 'Y' => 34,
            'Z' => 35, 'B' => 11
        );

        if (!preg_match("/\A[A-Z]{2}\d{2} ?[A-Z\d]{4}( ?\d{4}){1,} ?\d{1,4}\z/", $input[$field])) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }

        $iban = str_replace(' ', '', $input[$field]);
        $iban = substr($iban, 4) . substr($iban, 0, 4);
        $iban = strtr($iban, $character);

        if (bcmod($iban, 97) != 1) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Determine if the provided input is a valid date (ISO 8601).
     *
     * Usage: '<index>' => 'date'
     *
     * @param string $field
     * @param string $input date ('Y-m-d') or datetime ('Y-m-d H:i:s')
     * @param null   $param
     *
     * @return mixed
     */
    protected function validate_date($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        $cdate1 = date('Y-m-d', strtotime($input[$field]));
        $cdate2 = date('Y-m-d H:i:s', strtotime($input[$field]));

        if ($cdate1 != $input[$field] && $cdate2 != $input[$field]) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Determine if the provided input meets age requirement (ISO 8601).
     *
     * Usage: '<index>' => 'min_age,13'
     *
     * @param string $field
     * @param string $input date ('Y-m-d') or datetime ('Y-m-d H:i:s')
     * @param string $param int
     *
     * @return mixed
     */
    protected function validate_min_age($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        $cdate1 = new DateTime(date('Y-m-d', strtotime($input[$field])));
        $today = new DateTime(date('d-m-Y'));

        $interval = $cdate1->diff($today);
        $age = $interval->y;

        if ($age <= $param) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Determine if the provided numeric value is lower or equal to a specific value.
     *
     * Usage: '<index>' => 'max_numeric,50'
     *
     * @param string $field
     * @param array  $input
     * @param null   $param
     *
     * @return mixed
     */
    protected function validate_max_numeric($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (is_numeric($input[$field]) && is_numeric($param) && ($input[$field] <= $param)) {
            return;
        }

        return array(
            'field' => $field,
            'value' => $input[$field],
            'rule' => __FUNCTION__,
            'param' => $param,
        );
    }

    /**
     * Determine if the provided numeric value is higher or equal to a specific value.
     *
     * Usage: '<index>' => 'min_numeric,1'
     *
     * @param string $field
     * @param array  $input
     * @param null   $param
     * @return mixed
     */
    protected function validate_min_numeric($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (is_numeric($input[$field]) && is_numeric($param) && ($input[$field] >= $param)) {
            return;
        }

        return array(
            'field' => $field,
            'value' => $input[$field],
            'rule' => __FUNCTION__,
            'param' => $param,
        );
    }

    /**
     * Determine if the provided value starts with param.
     *
     * Usage: '<index>' => 'starts,Z'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_starts($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (strpos($input[$field], $param) !== 0) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Checks if a file was uploaded.
     *
     * Usage: '<index>' => 'required_file'
     *
     * @param  string $field
     * @param  array $input
     *
     * @return mixed
     */
    protected function validate_required_file($field, $input, $param = null) {
        if (!isset($input[$field])) {
            return;
        }

        if (is_array($input[$field]) && $input[$field]['error'] !== 4) {
            return;
        }

        return array(
            'field' => $field,
            'value' => $input[$field],
            'rule' => __FUNCTION__,
            'param' => $param,
        );
    }

    /**
     * Check the uploaded file for extension for now
     * checks only the ext should add mime type check.
     *
     * Usage: '<index>' => 'extension,png;jpg;gif
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_extension($field, $input, $param = null) {
        if (!isset($input[$field])) {
            return;
        }

        if (is_array($input[$field]) && $input[$field]['error'] !== 4) {
            $param = trim(strtolower($param));
            $allowed_extensions = explode(';', $param);

            $path_info = pathinfo($input[$field]['name']);
            $extension = $path_info['extension'];

            if (in_array($extension, $allowed_extensions)) {
                return;
            }

            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Determine if the provided field value equals current field value.
     *
     *
     * Usage: '<index>' => 'equalsfield,Z'
     *
     * @param string $field
     * @param string $input
     * @param string $param field to compare with
     *
     * @return mixed
     */
    protected function validate_equalsfield($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if ($input[$field] == $input[$param]) {
            return;
        }

        return array(
            'field' => $field,
            'value' => $input[$field],
            'rule' => __FUNCTION__,
            'param' => $param,
        );
    }

    /**
     * Determine if the provided field value is a valid GUID (v4)
     *
     * Usage: '<index>' => 'guidv4'
     *
     * @param string $field
     * @param string $input
     * @param string $param field to compare with
     * @return mixed
     */
    protected function validate_guidv4($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (preg_match("/\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/", $input[$field])) {
            return;
        }

        return array(
            'field' => $field,
            'value' => $input[$field],
            'rule' => __FUNCTION__,
            'param' => $param,
        );
    }

    /**
     * Trims whitespace only when the value is a scalar.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    private function trimScalar($value) {
        if (is_scalar($value)) {
            $value = trim($value);
        }

        return $value;
    }

    /**
     * Determine if the provided value is a valid phone number.
     *
     * Usage: '<index>' => 'phone_number'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     *
     * Examples:
     *
     *  555-555-5555: valid
     *  5555425555: valid
     *  555 555 5555: valid
     *  1(519) 555-4444: valid
     *  1 (519) 555-4422: valid
     *  1-555-555-5555: valid
     *  1-(555)-555-5555: valid
     */
    protected function validate_phone_number($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        $regex = '/^(\d[\s-]?)?[\(\[\s-]{0,2}?\d{3}[\)\]\s-]{0,2}?\d{3}[\s-]?\d{4}$/i';
        if (!preg_match($regex, $input[$field])) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Custom regex validator.
     *
     * Usage: '<index>' => 'regex,/your-regex-expression/'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_regex($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        $regex = $param;
        if (!preg_match($regex, $input[$field])) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * JSON validator.
     *
     * Usage: '<index>' => 'valid_json_string'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_valid_json_string($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (!is_string($input[$field]) || !is_object(json_decode($input[$field]))) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Check if an input is an array and if the size is more or equal to a specific value.
     *
     * Usage: '<index>' => 'valid_array_size_greater,1'
     *
     * @param string $field
     * @param array  $input
     *
     * @return mixed
     */
    protected function validate_valid_array_size_greater($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (!is_array($input[$field]) || sizeof($input[$field]) < (int) $param) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Check if an input is an array and if the size is less or equal to a specific value.
     *
     * Usage: '<index>' => 'valid_array_size_lesser,1'
     *
     * @param string $field
     * @param array $input
     *
     * @return mixed
     */
    protected function validate_valid_array_size_lesser($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (!is_array($input[$field]) || sizeof($input[$field]) > (int) $param) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

    /**
     * Check if an input is an array and if the size is equal to a specific value.
     *
     * Usage: '<index>' => 'valid_array_size_equal,1'
     *
     * @param string $field
     * @param array $input
     *
     * @return mixed
     */
    protected function validate_valid_array_size_equal($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return;
        }

        if (!is_array($input[$field]) || sizeof($input[$field]) == (int) $param) {
            return array(
                'field' => $field,
                'value' => $input[$field],
                'rule' => __FUNCTION__,
                'param' => $param,
            );
        }
    }

}
