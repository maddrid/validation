<?php

$Fields2 = array(
    'make' => array(
        'label' => __('Make'),
        'rule' => 'required|between:3,10|alpha_dash',
        'filter' => 'sanitize_string',
         'search' => 'true'
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
$Fields3 = array(
    'rooms' => array(

        'rule' => 'required|between:1,3|integer',
        'filter' => 'sanitize_numbers',
         'search' => 'true'
    ),
    'bathrooms' => array(

        'rule' => 'required|between:1,3|integer',
        'filter' => 'sanitize_numbers'
    ),
    'year' => array(

        'rule' => 'required|integer',
        'type' => 'select',
		'options' =>'1,2,3,4,5,6'
    )

);
dd($Fields2);
dd("PluginFields::newInstance()->setFields('item','\$Fields2','cars_attributes')");
PluginFields::newInstance()->setFields('item',$Fields2,'cars_attributes');
PluginFields::newInstance()->setFields('item',$Fields3,'real_estate');
dd("PluginFields::newInstance()->getFields('item')");
dd(PluginFields::newInstance()->getFields('item'));

dd("PluginFields::newInstance()->getField('item','gears')");
dd(PluginFields::newInstance()->getField('item','gears'));

dd("PluginFields::newInstance()->getRequired('item')");
dd(PluginFields::newInstance()->getRequired('item'));

dd("PluginFields::newInstance()->getSearch()");
dd(PluginFields::newInstance()->getSearch());

dd("PluginFields::newInstance()->getPlugins()");
dd(PluginFields::newInstance()->getPlugins());
