<?php

/*
 * this class should be used to work with the administrative side of wordpress
 */

class Daextctwga_Admin
{

    protected static $instance = null;
    private $shared = null;

	private $screen_id_help = null;
    private $screen_id_options = null;

    private function __construct()
    {

        //assign an instance of the plugin info
        $this->shared = Daextctwga_Shared::get_instance();

        //Load admin stylesheets and JavaScript
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        //Add the admin menu
        add_action('admin_menu', array($this, 'me_add_admin_menu'));

        //Load the options API registrations and callbacks
        add_action('admin_init', array($this, 'op_register_options'));

        //this hook is triggered during the creation of a new blog
        add_action('wpmu_new_blog', array($this, 'new_blog_create_options_and_tables'), 10, 6);

        //this hook is triggered during the deletion of a blog
        add_action('delete_blog', array($this, 'delete_blog_delete_options_and_tables'), 10, 1);

    }

    /*
     * return an instance of this class
     */
    public static function get_instance()
    {

        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;

    }

    /*
     * Enqueue admin specific styles.
     */
    public function enqueue_admin_styles()
    {

        $screen = get_current_screen();

	    //Menu Help
	    if ($screen->id == $this->screen_id_help) {

		    //Pro Version Menu
		    wp_enqueue_style($this->shared->get('slug') . '-menu-help',
			    $this->shared->get('url') . 'admin/assets/css/menu-help.css', array(), $this->shared->get('ver'));

	    }

        //Menu Options
        if ($screen->id == $this->screen_id_options) {

            //Framework Options
            wp_enqueue_style($this->shared->get('slug') . '-framework-options',
                $this->shared->get('url') . 'admin/assets/css/framework/options.css', array(),
                $this->shared->get('ver'));

            //jQuery UI Tooltip
            wp_enqueue_style($this->shared->get('slug') . '-jquery-ui-tooltip',
                $this->shared->get('url') . 'admin/assets/css/jquery-ui-tooltip.css', array(),
                $this->shared->get('ver'));

            //Select2
            wp_enqueue_style($this->shared->get('slug') . '-select2',
                $this->shared->get('url') . 'admin/assets/inc/select2/dist/css/select2.css', array(),
                $this->shared->get('ver'));
            wp_enqueue_style($this->shared->get('slug') . '-select2-custom',
                $this->shared->get('url') . 'admin/assets/css/select2-custom.css', array(), $this->shared->get('ver'));

        }

    }

    /*
     * Enqueue admin-specific JavaScript.
     */
    public function enqueue_admin_scripts()
    {

        $screen = get_current_screen();

        //Menu Options
        if ($screen->id == $this->screen_id_options) {

            //jQuery UI Tooltip
            wp_enqueue_script('jquery-ui-tooltip');
            wp_enqueue_script($this->shared->get('slug') . '-jquery-ui-tooltip-init',
                $this->shared->get('url') . 'admin/assets/js/jquery-ui-tooltip-init.js', 'jquery',
                $this->shared->get('ver'));

            //Select2
            wp_enqueue_script($this->shared->get('slug') . '-select2',
                $this->shared->get('url') . 'admin/assets/inc/select2/dist/js/select2.js', array('jquery'),
                $this->shared->get('ver'));
            wp_enqueue_script($this->shared->get('slug') . '-select2-init',
                $this->shared->get('url') . 'admin/assets/js/select2-init.js', array('jquery'),
                $this->shared->get('ver'));

        }

    }

    /*
     * plugin activation
     */
    public function ac_activate($networkwide)
    {

        /*
         * delete options and tables for all the sites in the network
         */
        if (function_exists('is_multisite') and is_multisite()) {

            /*
             * if this is a "Network Activation" create the options and tables
             * for each blog
             */
            if ($networkwide) {

                //get the current blog id
                global $wpdb;
                $current_blog = $wpdb->blogid;

                //create an array with all the blog ids
                $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

                //iterate through all the blogs
                foreach ($blogids as $blog_id) {

                    //swith to the iterated blog
                    switch_to_blog($blog_id);

                    //create options and tables for the iterated blog
                    $this->ac_initialize_options();

                }

                //switch to the current blog
                switch_to_blog($current_blog);

            } else {

                /*
                 * if this is not a "Network Activation" create options and
                 * tables only for the current blog
                 */
                $this->ac_initialize_options();

            }

        } else {

            /*
             * if this is not a multisite installation create options and
             * tables only for the current blog
             */
            $this->ac_initialize_options();

        }

    }

    //create the options and tables for the newly created blog
    public function new_blog_create_options_and_tables($blog_id, $user_id, $domain, $path, $site_id, $meta)
    {

        global $wpdb;

        /*
         * if the plugin is "Network Active" create the options and tables for
         * this new blog
         */
        if (is_plugin_active_for_network('conversion-tracking-for-woocommerce-and-google-ads/init.php')) {

            //get the id of the current blog
            $current_blog = $wpdb->blogid;

            //switch to the blog that is being activated
            switch_to_blog($blog_id);

            //create options and database tables for the new blog
            $this->ac_initialize_options();

            //switch to the current blog
            switch_to_blog($current_blog);

        }

    }

    //delete options and tables for the deleted blog
    public function delete_blog_delete_options_and_tables($blog_id)
    {

        global $wpdb;

        //get the id of the current blog
        $current_blog = $wpdb->blogid;

        //switch to the blog that is being activated
        switch_to_blog($blog_id);

        //create options and database tables for the new blog
        $this->un_delete_options();

        //switch to the current blog
        switch_to_blog($current_blog);

    }

    /*
     * initialize plugin options
     */
    private function ac_initialize_options()
    {

	    foreach($this->shared->get('options') as $key => $value){
		    add_option($key, $value);
	    }

    }

    /*
     * Plugin delete.
     */
    static public function un_delete()
    {

        /*
         * Delete options and tables for all the sites in the network.
         */
        if (function_exists('is_multisite') and is_multisite()) {

            //get the current blog id
            global $wpdb;
            $current_blog = $wpdb->blogid;

            //create an array with all the blog ids
            $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

            //iterate through all the blogs
            foreach ($blogids as $blog_id) {

                //switch to the iterated blog
                switch_to_blog($blog_id);

                //create options and tables for the iterated blog
                Daextctwga_Admin::un_delete_options();

            }

            //switch to the current blog
            switch_to_blog($current_blog);

        } else {

            /*
             * If this is not a multisite installation delete options and tables only for the current blog.
             */
            Daextctwga_Admin::un_delete_options();

        }

    }

    /*
     * Delete plugin options.
     */
    static public function un_delete_options()
    {

        //assign an instance of Daextctwga_Shared
        $shared = Daextctwga_Shared::get_instance();

	    foreach($shared->get('options') as $key => $value){
		    delete_option($key);
	    }

    }

    /*
     * Register the admin menu.
     */
    public function me_add_admin_menu()
    {

		$icon_svg = 'data:image/svg+xml;base64, PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyNS4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHZpZXdCb3g9IjAgMCAxOCAxOCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMTggMTg7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiMzQzhCRDk7fQ0KCS5zdDF7ZmlsbDojRkFCQzA0O30NCgkuc3Qye2ZpbGw6IzM0QTg1Mjt9DQoJLnN0M3tmaWxsOiNFMUMwMjU7fQ0KPC9zdHlsZT4NCjxnPg0KCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik02LjUsMy41YzAuMi0wLjQsMC40LTAuNywwLjctMS4xYzEuMy0xLjMsMy40LTAuOSw0LjIsMC42YzAuNiwxLjIsMS4zLDIuMywyLDMuNWMxLjEsMiwyLjIsMy44LDMuMyw1LjgNCgkJYzAuOSwxLjYtMC4xLDMuNy0xLjksMy45Yy0xLjIsMC4yLTIuMS0wLjQtMi44LTEuM2MtMS0xLjctMi0zLjQtMi45LTUuMUw5LDkuN2MwLTAuMS0wLjEtMC4yLTAuMS0wLjNDOC40LDguNyw4LDcuOSw3LjYsNy4xDQoJCWMtMC4zLTAuNC0wLjYtMS0wLjktMS40QzYuNCw1LjMsNi4zLDQuOCw2LjMsNC4zQzYuNCw0LDYuNCwzLjcsNi41LDMuNSIvPg0KCTxwYXRoIGNsYXNzPSJzdDEiIGQ9Ik02LjUsMy41YzAsMC4zLTAuMSwwLjUtMC4xLDAuN2MwLDAuNSwwLjEsMS4xLDAuNCwxLjVDNy41LDcsOC4yLDguMiw4LjksOS41QzksOS42LDksOS42LDkuMSw5LjcNCgkJYy0wLjQsMC43LTAuOCwxLjMtMS4yLDIuMWMtMC41LDEtMS4xLDEuOS0xLjYsMi45bDAsMGMwLTAuMSwwLTAuMSwwLTAuMmMwLjItMSwwLTEuOS0wLjYtMi42Yy0wLjQtMC40LTAuOS0wLjctMS41LTAuOA0KCQljLTAuOC0wLjEtMS40LDAuMS0yLjEsMC41Yy0wLjIsMC4xLTAuMywwLjMtMC40LDAuM2MwLDAsMCwwLTAuMSwwYzAuMy0wLjUsMC42LTEuMSwwLjktMS42QzMuOSw4LDUuMSw1LjgsNi41LDMuNQ0KCQlDNi41LDMuNiw2LjUsMy42LDYuNSwzLjUiLz4NCgk8cGF0aCBjbGFzcz0ic3QyIiBkPSJNMS43LDExLjljMC4xLTAuMSwwLjItMC4yLDAuNC0wLjNjMS42LTEuMywzLjktMC40LDQuMywxLjZjMC4xLDAuNCwwLDAuOS0wLjEsMS4zdjAuMQ0KCQljLTAuMSwwLjEtMC4xLDAuMi0wLjIsMC40Yy0wLjYsMS0xLjUsMS40LTIuNiwxLjNDMi4yLDE2LjMsMS4yLDE1LjMsMSwxNGMtMC4xLTAuNiwwLTEuMiwwLjQtMS43QzEuNSwxMi4yLDEuNiwxMi4xLDEuNywxMS45DQoJCUMxLjcsMTIsMS43LDExLjksMS43LDExLjkiLz4NCgk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNMS43LDExLjlDMS43LDEyLDEuNywxMiwxLjcsMTEuOUMxLjcsMTIsMS43LDExLjksMS43LDExLjlMMS43LDExLjkiLz4NCgk8cGF0aCBjbGFzcz0ic3QzIiBkPSJNNi4yLDE0LjZDNi4yLDE0LjYsNi4yLDE0LjYsNi4yLDE0LjZDNi4yLDE0LjYsNi4zLDE0LjYsNi4yLDE0LjZMNi4yLDE0LjYiLz4NCjwvZz4NCjwvc3ZnPg0K';

        add_menu_page(
            esc_html__('Conversion', 'daextctwga'),
            esc_html__('Conversion', 'daextctwga'),
            'manage_options',
            $this->shared->get('slug') . '-help',
            array($this, 'me_display_menu_help'),
            $icon_svg
        );

        $this->screen_id_help = add_submenu_page(
            $this->shared->get('slug') . '-help',
            esc_html__('Conversion - Help', 'daextctwga'),
            esc_html__('Help', 'daextctwga'),
            'manage_options',
            $this->shared->get('slug') . '-help',
            array($this, 'me_display_menu_help')
        );

        $this->screen_id_options = add_submenu_page(
            $this->shared->get('slug') . '-help',
            esc_html__('Conversion - Options', 'daextctwga'),
            esc_html__('Options', 'daextctwga'),
            'manage_options',
            $this->shared->get('slug') . '-options',
            array($this, 'me_display_menu_options')
        );

    }

	/*
     * includes the help view
     */
	public function me_display_menu_help()
	{
		include_once('view/help.php');
	}

    /*
     * includes the options view
     */
    public function me_display_menu_options()
    {
        include_once('view/options.php');
    }

    /*
     * register options
     */
    public function op_register_options()
    {

        //Tag Setup Section --------------------------------------------------------------------------------------------
        add_settings_section(
            'daextctwga_tag_setup_settings_section',
            null,
            null,
            'daextctwga_tag_setup_options'
        );

        add_settings_field(
            'conversion_id',
            esc_html__('Conversion ID', 'daextctwga'),
            array($this, 'conversion_id_callback'),
            'daextctwga_tag_setup_options',
            'daextctwga_tag_setup_settings_section'
        );

        register_setting(
            'daextctwga_tag_setup_options',
            'daextctwga_conversion_id',
            array($this, 'conversion_id_validation')
        );

        add_settings_field(
            'conversion_label',
            esc_html__('Conversion Label', 'daextctwga'),
            array($this, 'conversion_label_callback'),
            'daextctwga_tag_setup_options',
            'daextctwga_tag_setup_settings_section'
        );

        register_setting(
            'daextctwga_tag_setup_options',
            'daextctwga_conversion_label',
            array($this, 'conversion_label_validation')
        );

	    add_settings_field(
		    'order_value',
		    esc_html__('Order Value', 'daextctwga'),
		    array($this, 'order_value_callback'),
		    'daextctwga_tag_setup_options',
		    'daextctwga_tag_setup_settings_section'
	    );

	    register_setting(
		    'daextctwga_tag_setup_options',
		    'daextctwga_order_value',
		    array($this, 'order_value_validation')
	    );

	    add_settings_field(
		    'global_site_tag',
		    esc_html__('Global Site Tag', 'daextctwga'),
		    array($this, 'global_site_tag_callback'),
		    'daextctwga_tag_setup_options',
		    'daextctwga_tag_setup_settings_section'
	    );

	    register_setting(
		    'daextctwga_tag_setup_options',
		    'daextctwga_global_site_tag',
		    array($this, 'global_site_tag_validation')
	    );

	    //Advanced Section ---------------------------------------------------------------------------------------------
	    add_settings_section(
		    'daextctwga_advanced_settings_section',
		    null,
		    null,
		    'daextctwga_advanced_options'
	    );

	    add_settings_field(
            'excluded_user_capability',
            esc_html__('Excluded User Capability', 'daextctwga'),
            array($this, 'excluded_user_capability_callback'),
            'daextctwga_advanced_options',
            'daextctwga_advanced_settings_section'
        );

        register_setting(
            'daextctwga_advanced_options',
            'daextctwga_excluded_user_capability',
            array($this, 'excluded_user_capability_validation')
        );

	    add_settings_field(
		    'excluded_user_ip',
		    esc_html__('Excluded User IP', 'daextctwga'),
		    array($this, 'excluded_user_ip_callback'),
		    'daextctwga_advanced_options',
		    'daextctwga_advanced_settings_section'
	    );

	    register_setting(
		    'daextctwga_advanced_options',
		    'daextctwga_excluded_user_ip',
		    array($this, 'excluded_user_ip_validation')
	    );

	    add_settings_field(
		    'require_cookie',
		    esc_html__('Require Coookie', 'daextctwga'),
		    array($this, 'require_cookie_callback'),
		    'daextctwga_advanced_options',
		    'daextctwga_advanced_settings_section'
	    );

	    register_setting(
		    'daextctwga_advanced_options',
		    'daextctwga_require_cookie',
		    array($this, 'require_cookie_validation')
	    );

	    add_settings_field(
		    'required_cookie_name',
		    esc_html__('Coookie Name', 'daextctwga'),
		    array($this, 'required_cookie_name_callback'),
		    'daextctwga_advanced_options',
		    'daextctwga_advanced_settings_section'
	    );

	    register_setting(
		    'daextctwga_advanced_options',
		    'daextctwga_required_cookie_name',
		    array($this, 'required_cookie_name_validation')
	    );

	    add_settings_field(
		    'required_cookie_value',
		    esc_html__('Cookie Value', 'daextctwga'),
		    array($this, 'required_cookie_value_callback'),
		    'daextctwga_advanced_options',
		    'daextctwga_advanced_settings_section'
	    );

	    register_setting(
		    'daextctwga_advanced_options',
		    'daextctwga_required_cookie_value',
		    array($this, 'required_cookie_value_validation')
	    );

    }

    //Tag Setup options callbacks and validations ----------------------------------------------------------------------
	public function conversion_id_callback($args)
	{

		$html = '<input maxlength="255" type="text" id="daextctwga_conversion_id" name="daextctwga_conversion_id" class="regular-text" value="' . esc_attr(get_option("daextctwga_conversion_id")) . '" />';
		$html .= '<div class="help-icon" title="' . esc_attr__('The Google Ads Conversion ID.',
				'daextctwga') . '"></div>';
		echo $html;

	}

	public function conversion_id_validation($input)
	{

		return sanitize_text_field($input);

	}

	public function conversion_label_callback($args)
	{

		$html = '<input maxlength="255" type="text" id="daextctwga_conversion_label" name="daextctwga_conversion_label" class="regular-text" value="' . esc_attr(get_option("daextctwga_conversion_label")) . '" />';
		$html .= '<div class="help-icon" title="' . esc_attr__('The Google Ads Conversion Label.',
				'daextctwga') . '"></div>';
		echo $html;

	}

	public function conversion_label_validation($input)
	{

		return sanitize_text_field($input);

	}

	public function order_value_callback($args)
	{

		$html = '<select id="daextctwga-conversion-id" name="daextctwga_order_value" class="daext-display-none">';
		$html .= '<option ' . selected(intval(get_option("daextctwga_order_value")), 0,
				false) . ' value="0">' . esc_html__('Subtotal', 'daextctwga') . '</option>';
		$html .= '<option ' . selected(intval(get_option("daextctwga_order_value")), 1,
				false) . ' value="1">' . esc_html__('Total', 'daextctwga') . '</option>';
		$html .= '</select>';
		$html .= '<div class="help-icon" title="' . esc_attr__('Select "Subtotal" to not include tax and shipping in the order value or "Total" to include tax and shipping in the order value.',
				'daextctwga') . '"></div>';

		echo $html;

	}

	public function order_value_validation($input)
	{

		return intval($input, 10) == 1 ? '1' : '0';

	}

	public function global_site_tag_callback($args)
	{

		$html = '<select id="daextctwga-global-site-tag" name="daextctwga_global_site_tag" class="daext-display-none">';
		$html .= '<option ' . selected(intval(get_option("daextctwga_global_site_tag"), 10), 0,
				false) . ' value="0">' . esc_html__('No', 'daextctwga') . '</option>';
		$html .= '<option ' . selected(intval(get_option("daextctwga_global_site_tag"), 10), 1,
				false) . ' value="1">' . esc_html__('Yes', 'daextctwga') . '</option>';
		$html .= '</select>';
		$html .= '<div class="help-icon" title="' . esc_attr__('Select whether to include or not the Global Site Tag on the page.',
				'daextctwga') . '"></div>';

		echo $html;

	}

	public function global_site_tag_validation($input)
	{

		return intval($input, 10) == 1 ? '1' : '0';

	}

	//Advanced options callbacks and validations -----------------------------------------------------------------------
	public function excluded_user_capability_callback($args)
	{

		$html = '<textarea id="daextctwga_excluded_user_capability" name="daextctwga_excluded_user_capability">' . esc_html(get_option("daextctwga_excluded_user_capability")) . '</textarea>';
		$html .= '<div class="help-icon" title="' . esc_attr__('The Event Snippet associated with the Google Ads conversion will not be included if the page is visited by users that own one or more of the capabilities listed in this comma-separated list.', 'daextctwga') . '"></div>';
		echo $html;

	}

	public function excluded_user_capability_validation($input)
	{

		return sanitize_textarea_field($input);

	}

	public function excluded_user_ip_callback($args)
	{

		$html = '<textarea id="daextctwga_excluded_user_ip" name="daextctwga_excluded_user_ip">' . esc_html(get_option("daextctwga_excluded_user_ip")) . '</textarea>';
		$html .= '<div class="help-icon" title="' . esc_attr__('The Event Snippet associated with the Google Ads conversion will not be included if the page is visited by users with the IP addresses listed in this comma-separated list.', 'daextctwga') . '"></div>';
		echo $html;

	}

	public function excluded_user_ip_validation($input)
	{

		return sanitize_textarea_field($input);

	}

	public function require_cookie_callback($args)
	{

		$html = '<select id="daextctwga-require-cookie" name="daextctwga_require_cookie" class="daext-display-none">';
		$html .= '<option ' . selected(intval(get_option("daextctwga_require_cookie"), 10), 0,
				false) . ' value="0">' . esc_html__('No', 'daextctwga') . '</option>';
		$html .= '<option ' . selected(intval(get_option("daextctwga_require_cookie"), 10), 1,
				false) . ' value="1">' . esc_html__('Yes', 'daextctwga') . '</option>';
		$html .= '</select>';
		$html .= '<div class="help-icon" title="' . esc_attr__('If you select "Yes" the Global Site Tag and the Event Snippet will be added to the page only if the cookie specified in the "Cookie Name" and "Cookie Value" options is present.',
				'daextctwga') . '"></div>';

		echo $html;

	}

	public function require_cookie_validation($input)
	{

		return intval($input, 10) == 1 ? '1' : '0';

	}

	public function required_cookie_name_callback($args)
	{

		$html = '<input maxlength="255" type="text" id="daextctwga_required_cookie_name" name="daextctwga_required_cookie_name" class="regular-text" value="' . esc_attr(get_option("daextctwga_required_cookie_name")) . '" />';
		$html .= '<div class="help-icon" title="' . esc_attr__('The name of the cookie.',
				'daextctwga') . '"></div>';
		echo $html;

	}

	public function required_cookie_name_validation($input)
	{

		return sanitize_text_field($input);

	}

	public function required_cookie_value_callback($args)
	{

		$html = '<input maxlength="255" type="text" id="daextctwga_required_cookie_value" name="daextctwga_required_cookie_value" class="regular-text" value="' . esc_attr(get_option("daextctwga_required_cookie_value")) . '" />';
		$html .= '<div class="help-icon" title="' . esc_attr__('The value of the cookie. Leave this option empty to allow any value.',
				'daextctwga') . '"></div>';
		echo $html;

	}

	public function required_cookie_value_validation($input)
	{

		return sanitize_text_field($input);

	}

}