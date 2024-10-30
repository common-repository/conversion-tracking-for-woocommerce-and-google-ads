<?php

/*
 * this class should be used to stores properties and methods shared by the
 * admin and public side of wordpress
 */

class Daextctwga_Shared
{

    protected static $instance = null;

    private $data = array();

    private function __construct()
    {

	    //Set plugin textdomain
	    load_plugin_textdomain('daextctwga', false, 'conversion-tracking-for-woocommerce-and-google-ads/lang/');

		$this->data['slug'] = 'daextctwga';
		$this->data['ver']  = '1.00';
		$this->data['dir']  = substr(plugin_dir_path(__FILE__), 0, -7);
		$this->data['url']  = substr(plugin_dir_url(__FILE__), 0, -7);

		//Here are stored the plugin option with the related default values
		$this->data['options'] = [

			//Database Version -----------------------------------------------------------------------------------------
			$this->get('slug') . "_database_version" => "0",

			//Tag Setup Options ----------------------------------------------------------------------------------------
			$this->get('slug') . '_conversion_id' => '',
			$this->get('slug') . '_conversion_label' => '',
			$this->get('slug') . '_order_value' => '0',
			$this->get('slug') . '_global_site_tag' => '1',

			//Advanced Options -----------------------------------------------------------------------------------------
			$this->get('slug') . '_excluded_user_capability' => '',
			$this->get('slug') . '_excluded_user_ip' => '',
			$this->get('slug') . '_require_cookie' => '0',
			$this->get('slug') . '_required_cookie_name' => '',
			$this->get('slug') . '_required_cookie_value' => '',

		];
	}

    public static function get_instance()
    {

        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;

    }

    //retrieve data
    public function get($index)
    {
        return $this->data[$index];
    }

	/**
	 * Returns true if:
	 *
	 * - The "Require Cookie" option is set to "No".
	 * - The "Require Cookie" option is set to "Yes" and the defined cookie name and cookie value are present.
	 * - The "Require Cookie" option is set to "Yes", the defined cookie name is present and the defined cookie value is
	 * an empty string.
	 *
	 * Otherwise false is returned.
	 *
	 * @return bool
	 */
    public function is_required_cookie_available(){

	    $require_cookie = intval(get_option('daextctwga_require_cookie'), 10);
    	$required_cookie_name = get_option('daextctwga_required_cookie_name');
    	$required_cookie_value = get_option('daextctwga_required_cookie_value');

    	if($require_cookie === 0 or
	       empty($required_cookie_name)
	    ){
		    return true;
	    }

    	if(isset($_COOKIE[$required_cookie_name]) and
	       (empty($required_cookie_value) or (string)$_COOKIE[$required_cookie_name] === $required_cookie_value)){
    		return true;
	    }else{
    		return false;
	    }

    }

	/**
	 * Returns True if the user has one or more of the capabilities defined in the 'daextctwga_excluded_user_capability'
	 * option.
	 *
	 * @return bool
	 */
    public function user_has_excluded_capability(){

    	$excluded_user_capability = get_option('daextctwga_excluded_user_capability');
    	$excluded_user_capability = preg_replace('/\s/', '', $excluded_user_capability);
    	$excluded_user_capability_a = explode(',', $excluded_user_capability);

    	foreach($excluded_user_capability_a as $key => $excluded_user_capability){
    		if(current_user_can($excluded_user_capability)){
    			return true;
		    }
	    }

    	return false;

    }

	/**
	 * Returns True if the user has one of the IP addressed defined in the 'daextctwga_excluded_ip_address' option.
	 *
	 * @return bool
	 */
	public function user_has_excluded_ip(){

    	$user_ip = $this->get_ip_address();
		$excluded_user_ip = get_option('daextctwga_excluded_user_ip');
		$excluded_user_ip = preg_replace('/\s/', '', $excluded_user_ip);
		$excluded_user_ip_a = explode(',', $excluded_user_ip);

		foreach($excluded_user_ip_a as $key => $excluded_user_ip){
			if($excluded_user_ip === $user_ip){
				return true;
			}
		}

		return false;

	}

	/**
	 * Get the IP address of the user by using the methods provided by WooCommerce.
	 *
	 * @return string
	 */
	public function get_ip_address(){

		$ip_address = WC_Geolocation::get_ip_address();
		if($ip_address === '::1'){
			$ip_address = WC_Geolocation::get_external_ip_address();
		}

		return $ip_address;

	}

}