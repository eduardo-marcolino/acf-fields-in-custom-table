<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('ACF_FICT_Options')):

    class ACF_FICT_Options
{

        /** @var array Data used in the view. */
        public $view = array();

        /**
         * __construct
         *
         * Sets up the class functionality.
         *
         * @date    23/06/12
         * @since   5.0.0
         *
         * @param   void
         * @return  void
         */
        public function __construct()
    {

            // Add actions.
            add_action('admin_menu', array($this, 'admin_menu'), 20);
        }

        /**
         * admin_menu
         *
         * Adds the admin menu subpage.
         *
         * @date    28/09/13
         * @since   5.0.0
         *
         * @param   void
         * @return  void
         */
        public function admin_menu()
    {

            // Bail early if no show_admin.
            if (!acf_get_setting('show_admin')) {
                return;
            }

            // Bail early if no show_updates.
            if (!acf_get_setting('show_updates')) {
                return;
            }

            // Bail early if not a plugin (included in theme).
            if (!acf_is_plugin_active()) {
                return;
            }

            // Add submenu.
            $page = add_submenu_page('edit.php?post_type=acf-field-group', __('Custom Table', 'acffict'), __('Custom Table', 'acf'), acf_get_setting('capability'), 'acf-fict-options', array($this, 'html'));

            // Add actions to page.
            add_action("load-$page", array($this, 'load'));
        }

        /**
         * load
         *
         * Runs when loading the submenu page.
         *
         * @date    7/01/2014
         * @since   5.0.0
         *
         * @param   void
         * @return  void
         */
        public function load()
    {
            // Check save options
            if (acf_verify_nonce("ACF_FICT_save_options")) {
                $this->save_options();
            }
        }

        /**
         * save_options
         *
         * Saves the values to the option table
         *
         * @date    16/01/2014
         * @since   5.0.0
         *
         * @param   void
         * @return  void
         */
        public function save_options()
    {

            // Update values if existing
            if (isset($_POST['acfict_table_prefix_value'])) {
                $tablePrefixValue = sanitize_text_field($_POST['acfict_table_prefix_value']);

                if (empty($tablePrefixValue)) {
                    return acf_add_admin_notice(__("Table prefix value cannot be empty...", "acfict"), 'warning');
                }

                // Remove spaces including tabs and line
                $tablePrefixValue = preg_replace('/\s+/', '', $tablePrefixValue);
                update_option("acfict_table_prefix_value", $tablePrefixValue, true);
            }

            if (isset($_POST['acfict_disable_wp_post_meta_storage_value'])) {
                $disableWpPostMetaStorageValue = sanitize_text_field($_POST['acfict_disable_wp_post_meta_storage_value']);
                // Remove spaces including tabs and line ends
                $disableWpPostMetaStorageValue = preg_replace('/\s+/', '', $disableWpPostMetaStorageValue);
                update_option("acfict_disable_wp_post_meta_storage_value", $disableWpPostMetaStorageValue, true);
            }

            // Show success notice.
            acf_add_admin_notice(__("Options are successfully saved!", "acfict"), 'success');
        }

        /**
         * html
         *
         * Displays the submenu page's HTML.
         *
         * @date    7/01/2014
         * @since   5.0.0
         *
         * @param   void
         * @return  void
         */
        public function html()
    {
            acf_get_view(dirname(__FILE__) . '/acf-fict-options.view.php', $this->view);
        }
    }

    if (!function_exists('acf_new_instance')) {
        $filePath = ABSPATH . PLUGINDIR . '/advanced-custom-fields/includes/acf-utility-functions.php';

        // Check if the ACF Free version exists
        if (!file_exists($filePath)) {
            $filePath = ABSPATH . PLUGINDIR . '/advanced-custom-fields-pro/includes/acf-utility-functions.php';

            // Check if the ACF Pro version exists
            if (!file_exists($filePath)) {
                return;
            }
        }

        require_once $filePath;
        acf_new_instance('ACF_FICT_Options');
    }

endif; // class_exists check
