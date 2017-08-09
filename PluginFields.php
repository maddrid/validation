<?php

class PluginFields {


    private static $instance;
// Contain  fields  that have been set manually

    private $plugins = array();
    private $fields = array();
    private $required = array();
    private $search = array();


    public static function newInstance() {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    //    Instance only
    private function __construct() {

    }

    private function __clone() {

    }

    public function getField($type,$key) {
        if (isset($this->fields[$type][$key])) {
            return $this->fields[$type][$key];
        } else {
            return null;
        }
    }

    public function getFields($type) {
        return $this->fields[$type];
    }
	public function validFieldType($fieldType){
	$type = array('item','user','page','comment');
	if(in_array($fieldType,$type){
	return $fieldType;
	}else{
	return 'item';
	}
	
	}

    public function getPlugin($key) {
        if (isset($this->plugins[$key])) {
            return $this->plugins[$key];
        } else {
            return null;
        }
    }

    public function getPlugins() {
        return $this->plugins;
    }

    public function getRequired() {
        return $this->required;
    }

    public function getSearch() {
        return $this->search;
    }


    public function setFields($type = 'item',array $fields, $plugin = null) {

        if (!empty($fields)) {
            ksort($fields);




            foreach ($fields as $key => $field) {


                // Set Field Type
                $fields[$key]['type'] = ( empty($fields[$key]['type']) ) ? 'text' : $fields[$key]['type'];
                // Set Labels
                $fields[$key]['label'] = ( empty($fields[$key]['label']) ) ? __($this->setLabel($key)) : $fields[$key]['label'];


                // Set Required
                if (!empty($fields[$key]['required'])) {

                    $this->required[$key] = true;
                }

                if (isset($fields[$key]['rule'])) {
                    if (strpos($fields[$key]['rule'], 'required') !== false) {
                        if (!array_key_exists($this->required, $key)) {
                          $this->required[$key] = true;
                        }
                    }

                }


                // Set Field Type
                if (isset($fields[$key]['type']) && $fields[$key]['type'] == 'private') {
                    $privaterules[] = $key;
                }
                if (!empty($fields[$key]['search'])) {

                    $this->search[$key] = true;
                }


                osc_run_hook('process_field_hook', $key, $fields[$key]);
            }



            $this->private_rules = array_merge($this->private_rules, $privaterules);
            if ($plugin != null) {
                $this->plugins[$plugin] = array_keys($fields);
            }
			$valid = $this->validFieldType($type);
            $this->fields[$valid] = array_merge($this->fields, $fields);
        }
    }

    public function setLabel($string) {
        $string = ucwords(strtolower($string));

        foreach (array('-', '\'', '_') as $delimiter) {
            if (strpos($string, $delimiter) !== false) {
                $string = implode($delimiter, array_map('ucfirst', explode($delimiter, $string)));
                $string = str_replace('_', ' ', $string);
            }
        }
        return $string;
    }

}
