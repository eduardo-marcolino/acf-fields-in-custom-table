<?php
function ACF_FICT_options_page()
{
    // Verify nonce
    if (isset($_POST['ACF_FICT_options_form_nonce_value']) && wp_verify_nonce($_POST['ACF_FICT_options_form_nonce_value'], 'ACF_FICT_options_form')) {

        // Update values if existing
        if (isset($_POST['acfict_table_prefix_value'])) {
            $tablePrefixValue = sanitize_text_field($_POST['acfict_table_prefix_value']);
            // Remove spaces including tabs and line ends
            $tablePrefixValue = preg_replace('/\s+/', '', $tablePrefixValue);
            update_option("acfict_table_prefix_value", $tablePrefixValue, true);
        }

        if (isset($_POST['acfict_disable_wp_post_meta_storage_value'])) {
            $disableWpPostMetaStorageValue = sanitize_text_field($_POST['acfict_disable_wp_post_meta_storage_value']);
            // Remove spaces including tabs and line ends
            $disableWpPostMetaStorageValue = preg_replace('/\s+/', '', $disableWpPostMetaStorageValue);
            update_option("acfict_disable_wp_post_meta_storage_value", $disableWpPostMetaStorageValue, true);
        }

    }

    ?>
<div class="wrap acf-settings-wrap">
    <h1><?php _e('ACF: Fields in Custom Table', 'acfict');?></h1>
    <div class="acf-box" id="acf-license-information">
        <div class="title">
            <h3><?php _e('Options', 'acfict');?></h3>
        </div>
        <div class="inner">
            <p><?php _e('Here are some options you can easily change!', 'acfict');?></p>
            <form action="" method="post">
                <h3 class=""><?php _e('Custom table prefix', 'acfict');?></h3>
                <input style="margin-top: 12px;" type="text" name="acfict_table_prefix_value" value='<?php echo get_option("acfict_table_prefix_value", "acf_") ?>' placeholder="acf_">
                <h3 class=""><?php _e('Disable WP Post Meta storage', 'acfict');?></h3>
                <input type='hidden' value='false' name='acfict_disable_wp_post_meta_storage_value'>
                <input style="margin-top: 12px;" type='checkbox' value='true' <?php if (get_option("acfict_disable_wp_post_meta_storage_value", "false") == "true") {echo "checked";}?> name='acfict_disable_wp_post_meta_storage_value'>
                <?php wp_nonce_field("ACF_FICT_options_form", "ACF_FICT_options_form_nonce_value", true, true);?>
                <hr style="margin-top:18px;">
                <?php echo get_submit_button("Save options", 'primary', 'ACF_FICT_options_form'); ?>
            </form>
        </div>
    </div>
</div>
<?php
}