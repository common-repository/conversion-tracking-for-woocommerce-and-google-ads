<?php

/*
 * This class should be used to work with the public side of wordpress.
 */

class Daextctwga_Public
{

    //general class properties
    protected static $instance = null;
    private $shared = null;

    private function __construct()
    {

        //assign an instance of the plugin info
        $this->shared = Daextctwga_Shared::get_instance();

        //write in front-end head
        add_action('wp_head', array($this, 'wr_public_head'));

    }

    /*
     * Creates an instance of this class.
     */
    public static function get_instance()
    {

        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;

    }

    public function wr_public_head()
    {

        //Do not proceed if WooCommerce is not active
	    if ( !class_exists( 'WooCommerce' ) ) {
	        return;
	    }

	    $conversion_id = get_option("daextctwga_conversion_id");
	    $conversion_label = get_option("daextctwga_conversion_label");
	    $global_site_tag = intval(get_option("daextctwga_global_site_tag"), 10);

	    if(empty($conversion_id) or empty($conversion_label)){
		    return;
	    }

        if(!$this->shared->is_required_cookie_available()){
            return;
        }

	    if($this->shared->user_has_excluded_capability()){
		    return;
	    }

	    if($this->shared->user_has_excluded_ip()){
		    return;
	    }

	    ?>

        <?php if($global_site_tag === 1): ?>

            <!-- Global Site Tag -->
            <script async src="https://www.googletagmanager.com/gtag/js?id=AW-<?php echo esc_js($conversion_id); ?>"></script>
            <script>
              window.dataLayer = window.dataLayer || [];
              function gtag(){dataLayer.push(arguments);}
              gtag('js', new Date());
              gtag('config', 'AW-<?php echo esc_js($conversion_id); ?>');
            </script>

        <?php endif; ?>

	    <?php

	    if ( is_order_received_page() ) {

		    $order_key      = sanitize_key($_GET['key']);
		    $order          = new WC_Order( wc_get_order_id_by_order_key( $order_key ) );

		    if ( ! $order->has_status( 'failed' ) ) {

			    $order_value = intval(get_option("daextctwga_order_value"), 10);
			    $order_total    = $order_value === 1 ? $order->get_subtotal() - $order->get_total_discount() : $order->get_total();
			    $order_currency = $order->get_currency();

			    ?>

                <!-- Event Snippet -->
			    <script>
                  gtag('event', 'conversion', {
                    'send_to': 'AW-<?php echo esc_js($conversion_id); ?>/<?php echo esc_js($conversion_label); ?>',
                    'value': <?php echo $order_total; ?>,
                    'currency': '<?php echo esc_js($order_currency); ?>',
                    'transaction_id': '<?php echo intval($order->get_order_number(), 10); ?>'
                  });
			    </script>

			    <?php

		    }

	    }

    }

}