<?php

class OscValidationError {

    private $errors = array();
    private $private = array();
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

    public function __construct() {

    }

    public function getErrors() {
        return $this->errors;
    }

    public function addError($key,$message,$private = null) {
        $this->errors[] = $error;
        if (is_null($private)) {
            if ($this->isUnique($key, $message)) {
                $this->errors[$key][] = $message;
            }
        } else {
            if ($this->isUnique($key, $message, true)) {
                $this->private[$key][] = $message;
            }
        }

        return $this;
    }


     /**
     * Get the appropriate format based on the given format.
     *
     * @param  string  $format
     * @return string
     */
    protected function checkContainer($container)
    {
        return ($container === null) ? $this->errors : $this->private;
    }



    /**
     * Determine if a key and message combination already exists.
     *
     * @param  string  $key
     * @param  string  $message
     *
     * @return bool
     */
    protected function isUnique($key, $message, $container = null)
    {
        $container = $this->checkContainer($container);

        $messages = (array) $container;

        return !isset($messages[$key]) || !in_array($message, $messages[$key]);
    }

    public function translations() {

        $translations = array(
            'mismatch' => __("There is no validation rule for %s", 'validation'),
            'validate_required' => __("The %s field is required", 'validation'),
            'validate_valid_email' => __("The %s field is required to be a valid email address", 'validation'),
            'validate_max_len_1' => __("The %s field needs to be shorter than %s character", 'validation'),
            'validate_max_len_2' => __("The %s field needs to be shorter than %s characters", 'validation'),
            'validate_min_len_1' => __("The %s field needs to be longer than %s character", 'validation'),
            'validate_min_len_2' => __("The %s field needs to be longer than %s characters", 'validation'),
            'validate_exact_len_1' => __("The %s field needs to be exactly %s character in length", 'validation'),
            'validate_exact_len_2' => __("The %s field needs to be exactly %s characters in length", 'validation'),
            'validate_alpha' => __("The %s field may only contain alpha characters(a-z)", 'validation'),
            'validate_alpha_numeric' => __("The %s field may only contain alpha-numeric characters", 'validation'),
            'validate_alpha_dash' => __("The %s field may only contain alpha characters &amp; dashes", 'validation'),
            'validate_numeric' => __("The %s field may only contain numeric characters", 'validation'),
            'validate_integer' => __("The %s field may only contain a numeric value", 'validation'),
            'validate_boolean' => __("The %s field may only contain a true or false value", 'validation'),
            'validate_float' => __("The %s field may only contain a float value", 'validation'),
            'validate_valid_url' => __("The %s field is required to be a valid URL", 'validation'),
            'validate_url_exists' => __("The %s URL does not exist", 'validation'),
            'validate_valid_ip' => __("The %s field needs to contain a valid IP address", 'validation'),
            'validate_valid_cc' => __("The %s field needs to contain a valid credit card number", 'validation'),
            'validate_valid_name' => __("The %s field needs to contain a valid human name", 'validation'),
            'validate_contains' => __("The %s field needs to contain one of these values: %s", 'validation'),
            'validate_containsList' => __("The %s field needs contain a value from its drop down list", 'validation'),
            'validate_doesNotContainList' => __("The %s field contains a value that is not accepted", 'validation'),
            'validate_street_address' => __("The %s field needs to be a valid street address", 'validation'),
            'validate_date' => __("The %s field needs to be a valid date", 'validation'),
            'validate_min_numeric' => __("The %s field needs to be a numeric value, equal to, or higher than %s", 'validation'),
            'validate_max_numeric' => __("The %s field needs to be a numeric value, equal to, or lower than %s", 'validation'),
            'validate_starts' => __("The %s field needs to start with %s", 'validation'),
            'default' => __("The %s field is invalid", 'validation')
        );

        return $translations;
    }

    public function get_readable_errors($convert_to_string = false, $field_class = 'gump-field', $error_class = 'gump-error-message') {
        if (empty($this->errors)) {
            return ($convert_to_string) ? null : array();
        }

        $resp = array();

        foreach ($this->errors as $e) {

            $translations = self::translations();

            $field = ucwords(str_replace($this->fieldCharsToRemove, chr(32), $e['field']));

            // Let's fetch explicit field names if they exist
            if (array_key_exists($e['field'], $fields)) {
                $field = $fields[$e['field']];
            }

            // If field have a label get field label
            if (array_key_exists($e['field'], $labels)) {
                $field = $labels[$e['field']];
            }

            $field = "<span class='{$fieldClass}'>{$field}</span>";
            $param = "<span class='{$fieldWarningClass}'>{$e['param']}</span>";

            switch ($e['rule'])
            {
                case 'mismatch' :
                    $resp[] = sprintf($translations['mismatch'], $field);
                    break;
                case 'validate_required':
                    $resp[] = sprintf($translations['validate_required'], $field);
                    break;
                case 'validate_valid_email':
                    $resp[] = sprintf($translations['validate_valid_email'], $field);
                    break;
                case 'validate_max_len':
                    if ($param == 1) {
                        $resp[] = sprintf($translations['validate_max_len_1'], $field, $param);
                    } else {
                        $resp[] = sprintf($translations['validate_max_len_2'], $field, $param);
                    }
                    break;
                case 'validate_min_len':
                    if ($param == 1) {
                        $resp[] = sprintf($translations['validate_min_len_1'], $field, $param);
                    } else {
                        $resp[] = sprintf($translations['validate_min_len_2'], $field, $param);
                    }
                    break;
                case 'validate_exact_len':
                    if ($param == 1) {
                        $resp[] = sprintf($translations['validate_exact_len_1'], $field, $param);
                    } else {
                        $resp[] = sprintf($translations['validate_exact_len_2'], $field, $param);
                    }
                    break;
                case 'validate_alpha':
                    $resp[] = sprintf($translations['validate_alpha'], $field);
                    break;
                case 'validate_alpha_numeric':
                    $resp[] = sprintf($translations['validate_alpha_numeric'], $field);
                    break;
                case 'validate_alpha_dash':
                    $resp[] = sprintf($translations['validate_alpha_dash'], $field);
                    break;
                case 'validate_numeric':
                    $resp[] = sprintf($translations['validate_numeric'], $field);
                    break;
                case 'validate_integer':
                    $resp[] = sprintf($translations['validate_integer'], $field);
                    break;
                case 'validate_boolean':
                    $resp[] = sprintf($translations['validate_boolean'], $field);
                    break;
                case 'validate_float':
                    $resp[] = sprintf($translations['validate_float'], $field);
                    break;
                case 'validate_valid_url':
                    $resp[] = sprintf($translations['validate_valid_url'], $field);
                    break;
                case 'validate_url_exists':
                    $resp[] = sprintf($translations['validate_url_exists'], $field);
                    break;
                case 'validate_valid_ip':
                    $resp[] = sprintf($translations['validate_valid_ip'], $field);
                    break;
                case 'validate_valid_cc':
                    $resp[] = sprintf($translations['validate_valid_cc'], $field);
                    break;
                case 'validate_valid_name':
                    $resp[] = sprintf($translations['validate_valid_name'], $field);
                    break;
                case 'validate_contains':
                    $resp[] = sprintf($translations['validate_contains'], $field, implode(', ', $param));
                    break;
                case 'validate_containsList':
                    $resp[] = sprintf($translations['validate_containsList'], $field);
                    break;
                case 'validate_doesNotContainList':
                    $resp[] = sprintf($translations['validate_doesNotContainList'], $field);
                    break;
                case 'validate_street_address':
                    $resp[] = sprintf($translations['validate_street_address'], $field);
                    break;
                case 'validate_date':
                    $resp[] = sprintf($translations['validate_date'], $field);
                    break;
                case 'validate_min_numeric':
                    $resp[] = sprintf($translations['validate_min_numeric'], $field, $param);
                    break;
                case 'validate_max_numeric':
                    $resp[] = sprintf($translations['validate_max_numeric'], $field, $param);
                    break;
                case 'validate_starts':
                    $resp[] = sprintf($translations['validate_starts'], $field, $param);
                    break;
                default:
                    $resp[] = sprintf($translations['default'], $field);
            }
        }

        if (!$convert_to_string) {
            return $resp;
        } else {
            $buffer = '';
            foreach ($resp as $s) {
                osc_add_flash_error_message($s);
            }
            return $buffer;
        }
    }

}
