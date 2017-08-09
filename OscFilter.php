<<<<<<< HEAD
<?php

class OscFilter {

    protected $filter_methods = array();

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

    public static $basic_tags = '<br><p><a><strong><b><i><em><img><blockquote><code><dd><dl><hr><h1><h2><h3><h4><h5><h6><label><ul><li><span><sub><sup>';

    public function hasFilter($filter) {
        return (isset($this->filter_methods[$filter])) ? TRUE : FALSE;
    }

    public function setFilter($filter, $callback) {
        return $this->filter_methods[$filter] = $callback;
    }

    public function getFilters() {
        return $this->filter_methods;
    }

    public function filter(array $input, array $filterset) {
        foreach ($filterset as $field => $filters) {
            if (!array_key_exists($field, $input)) {
                continue;
            }

            $filters = explode('|', $filters);

            foreach ($filters as $filter) {
                $params = null;

                if (strstr($filter, ',') !== false) {
                    $filter = explode(',', $filter);

                    $params = array_slice($filter, 1, count($filter) - 1);

                    $filter = $filter[0];
                }

                if (is_callable(array($this, 'filter_' . $filter))) {
                    $method = 'filter_' . $filter;
                    $input[$field] = $this->$method($input[$field], $params);
                } elseif (function_exists($filter)) {
                    $input[$field] = $filter($input[$field]);
                } elseif (isset($this->filter_methods[$filter])) {
                    $input[$field] = call_user_func($this->filter_methods[$filter], $input[$field], $params);
                } else {
                    throw new Exception("Filter method '$filter' does not exist.");
                }
            }
        }

        return $input;
    }

    public function sanitize(array $input, array $fields = array(), $utf8_encode = true) {
        $magic_quotes = (bool) get_magic_quotes_gpc();

        if (empty($fields)) {
            $fields = array_keys($input);
        }

        $return = array();

        foreach ($fields as $field) {
            if (!isset($input[$field])) {
                continue;
            } else {
                $value = $input[$field];
                if (is_array($value)) {
                    $value = null;
                }
                if (is_string($value)) {
                    if ($magic_quotes === true) {
                        $value = stripslashes($value);
                    }

                    if (strpos($value, "\r") !== false) {
                        $value = trim($value);
                    }

                    if (function_exists('iconv') && function_exists('mb_detect_encoding') && $utf8_encode) {
                        $current_encoding = mb_detect_encoding($value);

                        if ($current_encoding != 'UTF-8' && $current_encoding != 'UTF-16') {
                            $value = iconv($current_encoding, 'UTF-8', $value);
                        }
                    }

                    $value = filter_var($value, FILTER_SANITIZE_STRING);
                }

                $return[$field] = $value;
            }
        }

        return $return;
    }

    /**
     * Replace noise words in a string (http://tax.cchgroup.com/help/Avoiding_noise_words_in_your_search.htm).
     *
     * Usage: '<index>' => 'noise_words'
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_noise_words($value, $params = null) {
        $value = preg_replace('/\s\s+/u', chr(32), $value);

        $value = " $value ";
        if (class_exists('StopwordsCache')) {
            $stopwords = new StopwordsCache();
            $words = $stopwords->load($params[0]);

            foreach ($words as $word) {
                $word = trim($word);

                $word = " $word "; // Normalize

                if (stripos($value, $word) !== false) {
                    $value = str_ireplace($word, chr(32), $value);
                }
            }
        } else {
            $words = explode(',', self::$en_noise_words);

            foreach ($words as $word) {
                $word = trim($word);

                $word = " $word "; // Normalize

                if (stripos($value, $word) !== false) {
                    $value = str_ireplace($word, chr(32), $value);
                }
            }
        }
        return trim($value);
    }

    /**
     * Remove all known punctuation from a string.
     *
     * Usage: '<index>' => 'rmpunctuataion'
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_rmpunctuation($value, $params = null) {
        return preg_replace("/(?![.=$'€%-])\p{P}/u", '', $value);
    }

    /**
     * Sanitize the string by removing any script tags.
     *
     * Usage: '<index>' => 'sanitize_string'
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_sanitize_string($value, $params = null) {
        return filter_var($value, FILTER_SANITIZE_STRING);
    }

    /**
     * Sanitize the string by urlencoding characters.
     *
     * Usage: '<index>' => 'urlencode'
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_urlencode($value, $params = null) {
        return filter_var($value, FILTER_SANITIZE_ENCODED);
    }

    /**
     * Sanitize the string by converting HTML characters to their HTML entities.
     *
     * Usage: '<index>' => 'htmlencode'
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_htmlencode($value, $params = null) {
        return filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
    }

    /**
     * Sanitize the string by removing illegal characters from emails.
     *
     * Usage: '<index>' => 'sanitize_email'
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_sanitize_email($value, $params = null) {
        return filter_var($value, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize the string by removing illegal characters from numbers.
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_sanitize_numbers($value, $params = null) {
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitize the string by removing illegal characters from float numbers.
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_sanitize_floats($value, $params = null) {
        return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Filter out all HTML tags except the defined basic tags.
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_basic_tags($value, $params = null) {
        return strip_tags($value, self::$basic_tags);
    }

     /**
     * Filter out all HTML tags except the defined basic tags.
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_strip_tags($value, $params = null) {
        return strip_tags($value);
    }


     function filter_sanitize_allcaps($value, $params = null) {
        if ( preg_match("/^([A-Z][^A-Z]*)+$/", $value) && !preg_match("/[a-z]+/", $value) ) {
            $value = ucfirst(strtolower($value));
        }
        return $value;
     }
    /**
     * Convert the provided numeric value to a whole number.
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_whole_number($value, $params = null) {
        return intval($value);
    }

    /**
     * Convert MS Word special characters to web safe characters.
     * [“, ”, ‘, ’, –, …] => [", ", ', ', -, ...]
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_ms_word_characters($value, $params = null) {
        $word_open_double = '“';
        $word_close_double = '”';
        $web_safe_double = '"';

        $value = str_replace(array($word_open_double, $word_close_double), $web_safe_double, $value);

        $word_open_single = '‘';
        $word_close_single = '’';
        $web_safe_single = "'";

        $value = str_replace(array($word_open_single, $word_close_single), $web_safe_single, $value);

        $word_em = '–';
        $web_safe_em = '-';

        $value = str_replace($word_em, $web_safe_em, $value);

        $word_ellipsis = '…';
        $web_safe_em = '...';

        $value = str_replace($word_ellipsis, $web_safe_em, $value);

        return $value;
    }

}
=======
<?php

class OscFilter {

    protected $filter_methods = array();

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

    public static $basic_tags = '<br><p><a><strong><b><i><em><img><blockquote><code><dd><dl><hr><h1><h2><h3><h4><h5><h6><label><ul><li><span><sub><sup>';

    public function hasFilter($filter) {
        return (isset($this->filter_methods[$filter])) ? TRUE : FALSE;
    }

    public function setFilter($filter, $callback) {
        return $this->filter_methods[$filter] = $callback;
    }

    public function getFilters() {
        return $this->filter_methods;
    }

    public function filter(array $input, array $filterset) {
        foreach ($filterset as $field => $filters) {
            if (!array_key_exists($field, $input)) {
                continue;
            }

            $filters = explode('|', $filters);

            foreach ($filters as $filter) {
                $params = null;

                if (strstr($filter, ',') !== false) {
                    $filter = explode(',', $filter);

                    $params = array_slice($filter, 1, count($filter) - 1);

                    $filter = $filter[0];
                }

                if (is_callable(array($this, 'filter_' . $filter))) {
                    $method = 'filter_' . $filter;
                    $input[$field] = $this->$method($input[$field], $params);
                } elseif (function_exists($filter)) {
                    $input[$field] = $filter($input[$field]);
                } elseif (isset($this->filter_methods[$filter])) {
                    $input[$field] = call_user_func($this->filter_methods[$filter], $input[$field], $params);
                } else {
                    throw new Exception("Filter method '$filter' does not exist.");
                }
            }
        }

        return $input;
    }

    public function sanitize(array $input, array $fields = array(), $utf8_encode = true) {
        $magic_quotes = (bool) get_magic_quotes_gpc();

        if (empty($fields)) {
            $fields = array_keys($input);
        }

        $return = array();

        foreach ($fields as $field) {
            if (!isset($input[$field])) {
                continue;
            } else {
                $value = $input[$field];
                if (is_array($value)) {
                    $value = null;
                }
                if (is_string($value)) {
                    if ($magic_quotes === true) {
                        $value = stripslashes($value);
                    }

                    if (strpos($value, "\r") !== false) {
                        $value = trim($value);
                    }

                    if (function_exists('iconv') && function_exists('mb_detect_encoding') && $utf8_encode) {
                        $current_encoding = mb_detect_encoding($value);

                        if ($current_encoding != 'UTF-8' && $current_encoding != 'UTF-16') {
                            $value = iconv($current_encoding, 'UTF-8', $value);
                        }
                    }

                    $value = filter_var($value, FILTER_SANITIZE_STRING);
                }

                $return[$field] = $value;
            }
        }

        return $return;
    }

    /**
     * Replace noise words in a string (http://tax.cchgroup.com/help/Avoiding_noise_words_in_your_search.htm).
     *
     * Usage: '<index>' => 'noise_words'
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_noise_words($value, $params = null) {
        $value = preg_replace('/\s\s+/u', chr(32), $value);

        $value = " $value ";
        if (class_exists('StopwordsCache')) {
            $stopwords = new StopwordsCache();
            $words = $stopwords->load($params[0]);

            foreach ($words as $word) {
                $word = trim($word);

                $word = " $word "; // Normalize

                if (stripos($value, $word) !== false) {
                    $value = str_ireplace($word, chr(32), $value);
                }
            }
        } else {
            $words = explode(',', self::$en_noise_words);

            foreach ($words as $word) {
                $word = trim($word);

                $word = " $word "; // Normalize

                if (stripos($value, $word) !== false) {
                    $value = str_ireplace($word, chr(32), $value);
                }
            }
        }
        return trim($value);
    }

    /**
     * Remove all known punctuation from a string.
     *
     * Usage: '<index>' => 'rmpunctuataion'
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_rmpunctuation($value, $params = null) {
        return preg_replace("/(?![.=$'€%-])\p{P}/u", '', $value);
    }

    /**
     * Sanitize the string by removing any script tags.
     *
     * Usage: '<index>' => 'sanitize_string'
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_sanitize_string($value, $params = null) {
        return filter_var($value, FILTER_SANITIZE_STRING);
    }

    /**
     * Sanitize the string by urlencoding characters.
     *
     * Usage: '<index>' => 'urlencode'
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_urlencode($value, $params = null) {
        return filter_var($value, FILTER_SANITIZE_ENCODED);
    }

    /**
     * Sanitize the string by converting HTML characters to their HTML entities.
     *
     * Usage: '<index>' => 'htmlencode'
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_htmlencode($value, $params = null) {
        return filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
    }

    /**
     * Sanitize the string by removing illegal characters from emails.
     *
     * Usage: '<index>' => 'sanitize_email'
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_sanitize_email($value, $params = null) {
        return filter_var($value, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize the string by removing illegal characters from numbers.
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_sanitize_numbers($value, $params = null) {
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitize the string by removing illegal characters from float numbers.
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_sanitize_floats($value, $params = null) {
        return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Filter out all HTML tags except the defined basic tags.
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_basic_tags($value, $params = null) {
        return strip_tags($value, self::$basic_tags);
    }

     /**
     * Filter out all HTML tags except the defined basic tags.
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_strip_tags($value, $params = null) {
        return strip_tags($value);
    }


     function filter_sanitize_allcaps($value, $params = null) {
        if ( preg_match("/^([A-Z][^A-Z]*)+$/", $value) && !preg_match("/[a-z]+/", $value) ) {
            $value = ucfirst(strtolower($value));
        }
        return $value;
     }
    /**
     * Convert the provided numeric value to a whole number.
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_whole_number($value, $params = null) {
        return intval($value);
    }

    /**
     * Convert MS Word special characters to web safe characters.
     * [“, ”, ‘, ’, –, …] => [", ", ', ', -, ...]
     *
     * @param string $value
     * @param array  $params
     *
     * @return string
     */
    protected function filter_ms_word_characters($value, $params = null) {
        $word_open_double = '“';
        $word_close_double = '”';
        $web_safe_double = '"';

        $value = str_replace(array($word_open_double, $word_close_double), $web_safe_double, $value);

        $word_open_single = '‘';
        $word_close_single = '’';
        $web_safe_single = "'";

        $value = str_replace(array($word_open_single, $word_close_single), $web_safe_single, $value);

        $word_em = '–';
        $web_safe_em = '-';

        $value = str_replace($word_em, $web_safe_em, $value);

        $word_ellipsis = '…';
        $web_safe_em = '...';

        $value = str_replace($word_ellipsis, $web_safe_em, $value);

        return $value;
    }

}
>>>>>>> 6bf0f5ec47c8db86e07fe722fe1d84edbce93a52
