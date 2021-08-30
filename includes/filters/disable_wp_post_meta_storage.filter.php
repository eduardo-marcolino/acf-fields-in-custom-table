<?php

if (get_option("acfict_disable_wp_post_meta_storage_value", "false") == "true") {
    add_filter('acf/pre_update_value', function ($default, $value, $post_id, $field) {
        if ($field[ACF_FICT::SETTINGS_ENABLED]) {
            return false;
        }

        return $default;
    }, 10, 4);
}
