<?php
/*
Plugin Name: ACF: Fields in Custom Table
Description: Stores ACF custom fields in a custom table instead of WordPress core meta tables.
Version: 0.5
Author: Eduardo Marcolino
Author URI: https://eduardomarcolino.com
Text Domain: acfict
Domain Path: /languages
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
GitHub Plugin URI: https://github.com/eduardo-marcolino/acf-fields-in-custom-table
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'ACF_FICT' ) )
{
  defined( 'ACF_FICT_PLUGIN_FILE' ) or define( 'ACF_FICT_PLUGIN_FILE', __FILE__ );

  include_once( plugin_dir_path( __FILE__ ).'includes/acfict-utility-functions.php' );

  acfict_include( 'includes/types/class-acfict-type.php' );
  acfict_include( 'includes/types/class-acfict-type-column.php' );

  acfict_include( 'includes/fields/class-acfict-field.php' );
  acfict_include( 'includes/fields/class-acfict-field-text.php' );
  acfict_include( 'includes/fields/class-acfict-field-email.php' );
  acfict_include( 'includes/fields/class-acfict-field-url.php' );
  acfict_include( 'includes/fields/class-acfict-field-password.php' );
  acfict_include( 'includes/fields/class-acfict-field-radio.php' );
  acfict_include( 'includes/fields/class-acfict-field-button_group.php' );
  acfict_include( 'includes/fields/class-acfict-field-oembed.php' );
  acfict_include( 'includes/fields/class-acfict-field-number.php' );
  acfict_include( 'includes/fields/class-acfict-field-range.php' );
  acfict_include( 'includes/fields/class-acfict-field-file.php' );
  acfict_include( 'includes/fields/class-acfict-field-image.php' );
  acfict_include( 'includes/fields/class-acfict-field-true_false.php' );
  acfict_include( 'includes/fields/class-acfict-field-color_picker.php' );
  acfict_include( 'includes/fields/class-acfict-field-date_picker.php' );
  acfict_include( 'includes/fields/class-acfict-field-date_time_picker.php' );
  acfict_include( 'includes/fields/class-acfict-field-time_picker.php' );
  acfict_include( 'includes/fields/class-acfict-field-textarea.php' );
  acfict_include( 'includes/fields/class-acfict-field-wysiwyg.php' );
  acfict_include( 'includes/fields/class-acfict-field-select.php' );
  acfict_include( 'includes/fields/class-acfict-field-checkbox.php' );
  acfict_include( 'includes/fields/class-acfict-field-link.php' );
  acfict_include( 'includes/fields/class-acfict-field-relationship.php' );
  acfict_include( 'includes/fields/class-acfict-field-post_object.php' );
  acfict_include( 'includes/fields/class-acfict-field-page_link.php' );
  acfict_include( 'includes/fields/class-acfict-field-taxonomy.php' );
  acfict_include( 'includes/fields/class-acfict-field-user.php' );

  final class ACF_FICT
  {

    private static $instance = null;
    const SETTINGS_TABLE_NAME = 'acfict_table_name';
    const SETTINGS_ENABLED = 'acfict_enabled';
    const SETTINGS_USE_PREFIX = 'acfict_use_prefix';

    public static function getInstance()
    {
      if (static::$instance === null) {
          static::$instance = new static();
      }

      return static::$instance;
    }

    private function __construct()
    {
      add_action( 'acf/field_group/admin_head', [$this, 'register_meta_box'] );
      add_action( 'acf/update_field_group', [$this, 'create_or_update_table'] );
      add_action( 'acf/save_post', [$this, 'store_fields_in_custom_table'], 1 );
      add_action( 'delete_post', [$this, 'delete_fields_in_custom_table'] );
      add_action( 'admin_notices', [$this, 'display_admin_notices'] );

      add_filter( 'acf/load_field', [$this, 'add_settings'] );
      add_filter( 'acf/load_value', [$this, 'load_field_from_custom_table'], 11, 3 );
      add_filter( "acf/validate_field_group", [$this, 'validate_field_group'] );

      load_plugin_textdomain( 'acfict', false, dirname( plugin_basename( ACF_FICT_PLUGIN_FILE ) ) . '/languages' );
    }

    public function register_meta_box() {
      add_meta_box('acf-field-acfict', 'ACF: Fields in Custom Table', [$this, 'render_meta_box'], 'acf-field-group', 'normal');
    }

    public function render_meta_box()
    {
      global $field_group;

      acf_render_field_wrap( [
        'label'			    => __('Enabled', 'acfict'),
        'instructions'	=> __( 'Enable Store fields in custom table for this field group?', 'acfict' ),
        'type'			    => 'true_false',
        'name'			    => self::SETTINGS_ENABLED,
        'key'           => self::SETTINGS_ENABLED,
        'prefix'		    => 'acf_field_group',
        'value'         => esc_attr(acf_maybe_get( $field_group, self::SETTINGS_ENABLED, false )),
        'ui'			      => 1,
      ] );

      acf_render_field_wrap( [
        'label'			    => __('Use Prefix', 'acfict'),
        'instructions'	=> __( 'Append table name with prefix', 'acfict' ),
        'type'			    => 'true_false',
        'name'			    => self::SETTINGS_USE_PREFIX,
        'key'           => self::SETTINGS_USE_PREFIX,
        'prefix'		    => 'acf_field_group',
        'value'         => esc_attr(acf_maybe_get( $field_group, self::SETTINGS_USE_PREFIX, true )),
        'ui'			      => 1,
        'conditional_logic' => [
          'field' => self::SETTINGS_ENABLED,
          'operator' => '==',
          'value' => '1',
        ]
      ] );

      acf_render_field_wrap( [
        'label'			=> __( 'Custom table name', 'acfict' ),
        'instructions'	=> __( 'Define the custom table name. Make sure it doesn\'t conflict with others tables names.', 'acfict' ),
        'type'			=> 'text',
        'name'			=> self::SETTINGS_TABLE_NAME,
        'prefix'		=> 'acf_field_group',
        'value'			=> esc_attr(acf_maybe_get( $field_group, self::SETTINGS_TABLE_NAME, false )),
        'prepend'   => $this->table_name(),
        'required'  => true,
        'wrapper'   => [
          'class' => self::SETTINGS_TABLE_NAME.'_wrapper'
        ],
        'conditional_logic' => [
          'field' => self::SETTINGS_ENABLED,
          'operator' => '==',
          'value' => '1',
        ]
      ] );

      ?>
        <script type="text/javascript">
        if( typeof acf !== 'undefined' ) {

          var field = acf.getField('acfict_use_prefix');
          var prefixEl = jQuery('.<?php echo self::SETTINGS_TABLE_NAME ?>_wrapper .acf-input-prepend');
          if(field.val() == false) {
            prefixEl.addClass('acf-hidden')
          }
          field.on('change', function(e){
            prefixEl.toggleClass('acf-hidden')
          })

          acf.newPostbox({
            'id': 'acf-field-acfict',
            'label': 'left'
          });

        }
        </script>
      <?php
    }

    public function add_settings( $field )
    {
      $field_group = acf_get_field_group( $field['parent'] );

      foreach ([
        self::SETTINGS_ENABLED      => false,
        self::SETTINGS_USE_PREFIX   => true,
        self::SETTINGS_TABLE_NAME   => null
      ] as $key => $defaul_value)
      {
        $field[$key] = acf_maybe_get( $field_group, $key, $defaul_value );
      }

      return $field;
    }

    public function store_fields_in_custom_table( $post_id )
    {
      global $wpdb;
      $values = [];

      foreach ( $_POST['acf'] as $key => $value )
      {
        $field = get_field_object( sanitize_key( $key ) );

        if (
          $field[self::SETTINGS_ENABLED] && $field['name'] &&
          $this->is_supported($field)
        ) {
          $column_name = acfict_sanitize_keyword($field['name']);
          $values[$this->table_name($field[self::SETTINGS_TABLE_NAME], $field[self::SETTINGS_USE_PREFIX])][$column_name] = apply_filters(
            'acfict_sanitize_'.$field['type'],
            $value,
            $field
          );
        }
      }

      foreach ( $values as $table_name => $data)
      {
        $data['post_id'] = $post_id;

        $wpdb->suppress_errors = true;
        $wpdb->show_errors = false;

        if ( false  === $wpdb->replace($table_name, $data ) )
        {
          $message = __('ACF: Fields in Custom Table error:', 'acfict').$wpdb->last_error;
          acfict_admin_notice_add($message, 'error');
        }
      }
    }

    public function delete_fields_in_custom_table($post_id)
    {
      global $wpdb;

      foreach ( acf_get_field_groups( ['post_id' => $post_id] ) as $field_group )
      {
        if (
          array_key_exists(self::SETTINGS_TABLE_NAME, $field_group) &&
          $field_group[self::SETTINGS_TABLE_NAME]
        ) {
          $wpdb->delete(
            $this->table_name($field_group[self::SETTINGS_TABLE_NAME], $field_group[self::SETTINGS_USE_PREFIX]),
            ['post_id' => $post_id]
          );
        }
      }
    }

    public function create_or_update_table( $field_group )
    {
      if ( !$field_group[self::SETTINGS_ENABLED]) {
        return;
      }

      $columns  = [];
      $fields   = acf_get_fields( $field_group );

      foreach ( $fields as $field )
      {
        $column_type = apply_filters('acfict_column_type_'.$field['type'], $field);
        if ( $column_type && is_a( $column_type, 'ACF_FICT_Type_Column') ) {
          $columns[] = $column_type->get_definition();
        }
      }

      $response = $this->do_create_or_alter_table(
        $this->table_name( $field_group[self::SETTINGS_TABLE_NAME], $field_group[self::SETTINGS_USE_PREFIX] ),
        $columns
      );

      if ( $response !== true ) {
        $message = __('ACF: Fields in Custom Table error:', 'acfict').$response;
        acfict_admin_notice_add($message, 'error');
      }
    }

    private function do_create_or_alter_table( $table_name, $columns )
    {
      if (count($columns) === 0 ) {
        return 'No supported fields';
      }

      global $wpdb;

      $wpdb->suppress_errors = true;
      $wpdb->show_errors = false;

      $sql = "CREATE TABLE $table_name (
        post_id bigint(20) unsigned NOT NULL,
        ".join(",\n\t", $columns).(count($columns) > 0 ? ',' : '')."
        PRIMARY  KEY (post_id)
      ) ENGINE=InnoDB {$wpdb->get_charset_collate()};";

      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      $response = dbDelta( $sql );

      if ( $wpdb->last_error ) {
        return $wpdb->last_error;
      }

      if ( count($response) > 0 )
      {
        $notice = __('ACF: Fields in Custom Table: Database Changes', 'acfict');
        $notice .= '<table class="wp-list-table widefat fixed striped table-view-list posts">';
        $notice .= '<thead><tr><th>'.__('Column Name', 'acfict').'</th><th>'.__('Modification', 'acfict').'</th></tr></thead><tbody>';
        foreach( $response as $column => $text) {
          $notice .= sprintf('<tr><td>%s</td><td>%s</td></tr>', $column, $text);
        }
        $notice .= '</tbody></table>';
        acfict_admin_notice_add($notice, 'info');
      }

      return true;
    }

    public function load_field_from_custom_table( $value, $post_id, $field )
    {
      if (
        !$this->is_supported( $field ) ||
        !array_key_exists( self::SETTINGS_ENABLED, $field ) ||
        ( array_key_exists( self::SETTINGS_ENABLED, $field )  && !$field[self::SETTINGS_ENABLED] )
      ) {
        return $value;
      }

      $table_name = $this->table_name( $field[self::SETTINGS_TABLE_NAME], $field[self::SETTINGS_USE_PREFIX] );
      if ( $this->table_exists( $table_name ) )
      {
        global $wpdb;

        $column_name = sanitize_key($field['name']);

        $value = $wpdb->get_var( $wpdb->prepare(
          "SELECT $column_name FROM $table_name WHERE post_id = %d", $post_id
        ));

        return apply_filters('acfict_escape_'.$field['type'], $value, $field);
      }

      return $value;
    }

    public function validate_field_group( $field_group )
    {
      if ( array_key_exists( self::SETTINGS_TABLE_NAME, $field_group ) ) {
        $field_group[self::SETTINGS_TABLE_NAME] = acfict_sanitize_keyword( $field_group[self::SETTINGS_TABLE_NAME] );
      }
      return $field_group;
    }

    private function table_name( $name = '', $use_prefix = true) {
      global $wpdb;
      $prefix = $use_prefix ? apply_filters('acfict_table_prefix', $wpdb->prefix.'acf_', $name) : '';
      return $prefix.$name;
    }

    private function is_supported( $field ) {
      return apply_filters('acfict_supports_'.$field['type'], false);
    }

    private function table_exists( $table_name )
    {
      global $wpdb;
      $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );
      return $wpdb->get_var( $query ) === $table_name;
    }

    public function display_admin_notices()
    {
      if ( false !== ($message = acfict_admin_notice_get()))
      {
        echo sprintf('<div class="notice notice-%s"><p>%s</p></div>',
          $message['status'],
          $message['message']
        );
      }
    }
  }

  ACF_FICT::getInstance();
}

