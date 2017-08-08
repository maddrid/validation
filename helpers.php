<?php

function value($value)
{
    return $value instanceof Closure ? $value() : $value;
}

function studly_case($value)
{

    $value = ucwords(str_replace(['-', '_'], ' ', $value));

    return str_replace(' ', '', $value);
}

function array_get($array, $key, $default = null)
{
    if (is_null($key)) {
        return $array;
    }

    if (isset($array[$key])) {
        return $array[$key];
    }

    foreach (explode('.', $key) as $segment) {
        if (!is_array($array) || !array_key_exists($segment, $array)) {
            return value($default);
        }

        $array = $array[$segment];
    }

    return $array;
}

function osc_add_validation_rule ($field,$rules){
    OscValidationRule::newInstance()->mergeRules($field,$rules);

}

function getValidationRules( array $data)
{
	// @assert $key is a non-empty string
	// @assert $data is a loopable array
	// @otherwise return $default value
	if (!is_string($key) || empty($key) || !count($data))
	{
		return $default;
	}
	// @assert $key contains a dot notated string
	if (strpos($key, '.') !== false)
	{
		$keys = explode('.', $key);
		foreach ($keys as $innerKey)
		{
			// @assert $data[$innerKey] is available to continue
			// @otherwise return $default value
			if (!array_key_exists($innerKey, $data))
			{
				return $default;
			}
			$data = $data[$innerKey];
		}
		return $data;
	}
	// @fallback returning value of $key in $data or $default value
	return array_key_exists($key, $data) ? $data[$key] : $default;
}

?>