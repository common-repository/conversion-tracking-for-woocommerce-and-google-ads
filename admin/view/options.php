<?php

if ( ! current_user_can('manage_options')) {
    wp_die(esc_html__('You do not have sufficient capabilities to access this page.', 'daextctwga'));
}

?>

<div class="wrap">

    <h2><?php esc_attr_e('Conversion Tracking for WooCommerce and Google Ads - Options', 'daextctwga'); ?></h2>

    <?php

    //settings errors
    if (isset($_GET['settings-updated']) and $_GET['settings-updated'] == 'true') {
        settings_errors();
    }

    ?>

    <div id="daext-options-wrapper">

        <?php
        //get current tab value
        $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'tag_setup_options';
        ?>

        <div class="nav-tab-wrapper">
            <a href="?page=daextctwga-options&tab=tag_setup_options"
               class="nav-tab <?php echo $active_tab == 'tag_setup_options' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Tag Setup',
                    'daextctwga'); ?></a>
            <a href="?page=daextctwga-options&tab=advanced_options"
               class="nav-tab <?php echo $active_tab == 'advanced_options' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Advanced',
				    'daextctwga'); ?></a>
        </div>

        <form method="post" action="options.php" autocomplete="off">

            <?php

            if ($active_tab == 'tag_setup_options') {

                settings_fields($this->shared->get('slug') . '_tag_setup_options');
                do_settings_sections($this->shared->get('slug') . '_tag_setup_options');

            }

            if ($active_tab == 'advanced_options') {

	            settings_fields($this->shared->get('slug') . '_advanced_options');
	            do_settings_sections($this->shared->get('slug') . '_advanced_options');

            }

            ?>

            <div class="daext-options-action">
                <input type="submit" name="submit" id="submit" class="button"
                       value="<?php esc_attr_e('Save Changes', 'daextctwga'); ?>">
            </div>

        </form>

    </div>

</div>

