<?php

/*
Plugin Name: Conversion Tracking for WooCommerce and Google Ads
Description: Tracks the Google Ads conversion performed with WooCommerce.
Version: 1.00
Author: DAEXT
Author URI: https://daext.com
*/

//Prevent direct access to this file
if ( ! defined('WPINC')) {
    die();
}

//Class shared across public and admin
require_once(plugin_dir_path(__FILE__) . 'shared/class-daextctwga-shared.php');

//Public
require_once(plugin_dir_path(__FILE__) . 'public/class-daextctwga-public.php');
add_action('plugins_loaded', array('Daextctwga_Public', 'get_instance'));

//Admin
if (is_admin() && ( ! defined('DOING_AJAX') || ! DOING_AJAX)) {

    //Admin
    require_once(plugin_dir_path(__FILE__) . 'admin/class-daextctwga-admin.php');
    add_action('plugins_loaded', array('Daextctwga_Admin', 'get_instance'));

    //Activate
    register_activation_hook(__FILE__, array(Daextctwga_Admin::get_instance(), 'ac_activate'));

}