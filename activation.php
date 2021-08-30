<?php

function ACF_FICT_activation_check()
{
    // Require ACF Free or ACF Pro plugin
    if (!is_plugin_active('advanced-custom-fields/acf.php') || !is_plugin_active('advanced-custom-fields-pro/acf.php')) {
        // Stop activation redirect and show error
        wp_die(__('Sorry, but this plugin requires the ACF Free or ACF Pro plugin to be installed and active.', 'acfict') . '<br><br><a href="' . admin_url('plugins.php') . '">' . __('Return to plugins', 'acfict') . '</a>');
    }
}
