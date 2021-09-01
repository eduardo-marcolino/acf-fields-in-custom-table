<?php

function ACF_FICT_uninstall_check()
{
    if (!defined("WP_UNINSTALL_PLUGIN")) {
        die;
    }

    // Remove all the options
    $optionKeys = ["acfict_table_prefix_value", "acfict_disable_wp_post_meta_storage_value"];
    foreach ($optionKeys as $optionKey) {
        delete_option($optionKey);
    }
}
