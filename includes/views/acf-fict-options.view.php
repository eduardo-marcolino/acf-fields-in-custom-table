<?php

// vars
$nonce = 'ACF_FICT_save_options';
$button = __('Save Options', 'acfict');

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
                <?php acf_nonce_input($nonce);?>
                <h3 class=""><?php _e('Custom table prefix', 'acfict');?></h3>
                <input style="margin-top: 12px;" type="text" name="acfict_table_prefix_value" value='<?php echo get_option("acfict_table_prefix_value", "acf_") ?>' placeholder="acf_">
                <h3 class=""><?php _e('Disable WP Post Meta storage', 'acfict');?></h3>
                <input type='hidden' value='false' name='acfict_disable_wp_post_meta_storage_value'>
                <input style="margin-top: 12px;" type='checkbox' value='true' <?php if (get_option("acfict_disable_wp_post_meta_storage_value", "false") == "true") {echo "checked";}?> name='acfict_disable_wp_post_meta_storage_value'>
                <hr style="margin-top:18px;">
                <input type="submit" value="<?php echo esc_attr($button); ?>" class="button button-primary">
            </form>
        </div>
    </div>
</div>