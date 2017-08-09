<?php


$Fields = array(
    'contactName' => array(
        'label' => __('Username'),
        'rule' => 'between:3,10|alpha_dash',
        'filter' => 'sanitize_string'
    ),
    'contactEmail' => array(
        'label' => __('Contact Email'),
        'rule' => 'required|email',
        'filter' => 'sanitize_string'
    ),
    'userId' => array(
        'label' => __('User Id'),
        'rule' => 'required|numeric',
        'type' => 'private'
    ),
    'regionId' => array(
        'label' => __('Region Id'),
        'rule' => 'required|numeric',
        'filter' => 'sanitize_string'
    ),
    'price' => array(
        'label' => __('price'),
        'rule' => 'required|numeric|max:9999999999|min:5',
        'filter' => 'sanitize_string'
    ),
    'email' => array(
        'label' => __('E-mail'),
        'rule' => 'email',
        'filter' => 'sanitize_email'
    ),
    'currency' => array(
        'label' => __('Currency'),
        'rule' => 'required',
        'filter' => 'sanitize_email'
    ),
    'description' => array(
        'label' => __('Description'),
        'rule' => 'required|between:20,255',
        'multi' => 'en_US,es_ES',
        'filter' => 'sanitize_string|noise_words:en_US',
        'format' => 'ucwords'
    ),
    'title' => array(
        'label' => __('Titles'),
        'rule' => 'array|between:67,5000',
       'multi' => '*',
        'filter' => 'sanitize_string'
    ),
    'title.es_ES' => array(
        'label' => __(' The Title'),
        'rule' => 'between:5,5000',

        'filter' => 'noise_words:es_ES'
    ),
    'title.en_US' => array(
        'label' => __(' The Title'),
        'rule' => 'between:6,5000',

        'filter' => 'noise_words:en_US'
    )
);

$Fields2 = array(
    'make' => array(
        'label' => __('Make'),
        'rule' => 'required|between:3,10|alpha_dash',
        'filter' => 'sanitize_string'
    ),
    'model' => array(
        'label' => __('Model'),
        'rule' => 'required|between:3,10|',
        'filter' => 'sanitize_string'
    ),
    'gears' => array(
        'label' => __('Gears'),
        'rule' => 'required|numeric',
        'type' => 'select',
		'options' =>'1,2,3,4,5,6'
    )

);
$post = array(
    'contactName' => "tony",
    'contactEmail' => "tony@yahoo.com",
    'userId' => '23a',
    'active' => "ACTIVE",
    'catId' => "9",
    'countryId' => "ES",
    'country' => "",
    'region' => "",
    'regionId' => "781291",
    'city' => "",
    'cityId' => "197298",
    'price' => "34000000",
    'cityArea' => "",
    'address' => "Carrer De Casp Nr 45",
    'currency' => "USD",
    'showEmail' => '1',
    'title' => array('en_US' => "This is the english title ", 'es_ES' => "Mi titulo español"),
    'description' => array('en_US' => "asddfdgs sdf sdf er nertert ert ertert ert ert er ter"),
    'photos' => "",
    's_ip' => "::1",
    'd_coord_lat' => NULL,
    'd_coord_long' => NULL,
    's_zip' => NULL,
    'dt_expiration' => "0",
    'countryName' => "Spain",
    'regionName' => "Barcelona",
    'cityName' => "Barcelona");


PluginFields::newInstance()->setFields('item',$Fields);
PluginFields::newInstance()->setFields('item',$Fields2,'cars_attributes');
//dd(PluginFields2::newInstance()->getPlugins());
dd(OscValidationRule::newInstance()->getValidationRules());
//$validator = OscValidation::newInstance();
//$x = microtime(true);
//$valid = $validator->validate($POST,$post_rules);
//$temp = microtime(true) - $x;
//echo $temp;
//
//if($valid === true) {
//	echo "Validation passed!";
//} else {
//	dd(OscValidationError::newInstance()->getErrors()) ;
//}




?>
