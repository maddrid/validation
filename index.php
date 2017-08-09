<?php

/*
  Plugin Name: Validation
  Plugin Order: 5
  Plugin URI: http://www.blabla.es/
  Description: Validation plugin
  Version: 1.0.2
  Author: We
  Author URI: http://www.blabla.com/
  Short Name: validation

 */
 define('VALIDATION_ADMIN_LINK', osc_admin_render_plugin_url(osc_plugin_path(dirname(__FILE__))) . '/');



require 'OscValidation.php';
require 'PluginFields.php';
require 'helpers.php';
function validation_install() {

}

if(!function_exists('dd')){
    function dd($var) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

}



function validation_uninstall() {

}
   function validation_admin_menu() {
        osc_add_admin_menu_page('TEST', VALIDATION_ADMIN_LINK . 'dashboard.php', 'validation');

        osc_add_admin_submenu_page('validation', 'Plugin Fields', VALIDATION_ADMIN_LINK . 'plugin_fields.php', 'validation_plugin');

        osc_add_admin_submenu_page('validation', 'Test', VALIDATION_ADMIN_LINK . 'test_validation.php', 'validation_test');
        osc_add_admin_submenu_page('validation', 'Example', VALIDATION_ADMIN_LINK . 'full_example.php', 'validation_example');
    }

  osc_add_hook('admin_menu_init', 'validation_admin_menu');


osc_register_plugin(osc_plugin_path(__FILE__), 'validation_install');

osc_add_hook(osc_plugin_path(__FILE__) . "_uninstall", 'validation_uninstall');
?>
