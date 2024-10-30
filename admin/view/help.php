<?php

if ( ! current_user_can('manage_options')) {
    wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'daextctwga'));
}

?>

<!-- output -->

<div class="wrap">

    <h2><?php esc_html_e('Conversion Tracking for WooCommerce and Google Ads - Help', 'daextctwga'); ?></h2>

    <div id="daext-menu-wrapper">

        <p><?php esc_html_e('Visit the resources below to find your answers or to ask questions directly to the plugin developers.',
                'daextctwga'); ?></p>
        <ul>
            <li><a href="https://daext.com/doc/conversion-tracking-for-woocommerce-and-google-ads/"><?php esc_html_e('Plugin Documentation', 'daextctwga'); ?></a></li>
            <li><a href="https://daext.com/support/"><?php esc_html_e('Support Conditions', 'daextctwga'); ?></li>
            <li><a href="https://daext.com"><?php esc_html_e('Developer Website', 'daextctwga'); ?></a></li>
            <li><a href="https://wordpress.org/plugins/conversion-tracking-for-woocommerce-and-google-ads/"><?php esc_html_e('WordPress.org Plugin Page', 'daextctwga'); ?></a></li>
            <li><a href="https://wordpress.org/support/plugin/conversion-tracking-for-woocommerce-and-google-ads/"><?php esc_html_e('WordPress.org Support Forum', 'daextctwga'); ?></a></li>
        </ul>
        <p>

    </div>

</div>

