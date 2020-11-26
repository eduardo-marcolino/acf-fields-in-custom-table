<?php
/*
Plugin Name: ACF: Fields in Custom Table
Description: Stores ACF custom fields in a custom table instead of WordPress core meta tables.
Version: 0.1
Author: Eduardo Marcolino
Author URI: https://eduardomarcolino.com
Text Domain: acffict
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


  final class ACF_FICT
  {

    private static $instance = null;
    const SETTINGS_TABLE_NAME = 'acffict_table_name';
    const SETTINGS_ENABLED = 'acffict_enabled';

    public static function getInstance()
    {
      if (static::$instance === null) {
          static::$instance = new static();
      }

      return static::$instance;
    }

    private function __wakeup() {}

    private function __construct()
    {
      add_action( 'acf/field_group/admin_head', [$this, 'registerMetaBox'] );
      add_action( 'acf/update_field_group', [$this, 'createOrUpdateTable'] );
      add_action( 'acf/save_post', [$this, 'storeFieldsInCustomTable'], 1 );
      add_action( 'delete_post', [$this, 'deleteFieldsInCustomTable'] );

      add_action( 'admin_notices', function()
      {
        if ( false !== ($message = $this->getAdminNotice()))
        {
          echo sprintf('<div class="notice notice-%s"><p>%s</p></div>',
            $message['status'],
            $message['message']
          );
        }
      });

      add_filter( 'acf/load_field', [$this, 'addSettingsData'] );
      add_filter( 'acf/load_value', [$this, 'loadFieldFromCustomTable'], 11, 3 );
      add_filter( "acf/validate_field_group", [$this, 'validateFieldGroup'] );

      load_plugin_textdomain( 'acffict', false, dirname( plugin_basename( ACF_FICT_PLUGIN_FILE ) ) . '/languages' );
    }

    public function registerMetaBox() {
      add_meta_box('acf-field-acffict', 'ACF: Fields in Custom Table', [$this, 'renderMetaBox'], 'acf-field-group', 'normal');
    }

    public function renderMetaBox( )
    {
      global $field_group;

      acf_render_field_wrap( [
        'label'			    => __('Enabled', 'acffict'),
        'instructions'	=> __( 'Enable Store fields in custom table for this field group?', 'acffict' ),
        'type'			    => 'true_false',
        'name'			    => self::SETTINGS_ENABLED,
        'key'           => self::SETTINGS_ENABLED,
        'prefix'		    => 'acf_field_group',
        'value'         => esc_attr(acf_maybe_get( $field_group, self::SETTINGS_ENABLED, false )),
        'ui'			      => 1,
      ] );

      acf_render_field_wrap( [
        'label'			=> _( 'Custom table name', 'acffict' ),
        'instructions'	=> __( 'Define the custom table name. Make sure it doesn\'t conflict with others tables names.', 'acffict' ),
        'type'			=> 'text',
        'name'			=> self::SETTINGS_TABLE_NAME,
        'prefix'		=> 'acf_field_group',
        'value'			=> esc_attr(acf_maybe_get( $field_group, self::SETTINGS_TABLE_NAME, false )),
        'prepend'   => $this->getTableName(),
        'required'  => true,
        'conditional_logic' => [
          'field' => self::SETTINGS_ENABLED,
          'operator' => '==',
          'value' => '1',
        ]
      ] );

      ?>
        <script type="text/javascript">
        if( typeof acf !== 'undefined' ) {

          acf.newPostbox({
            'id': 'acf-field-acffict',
            'label': 'left'
          });

        }
        </script>
      <?php
    }

    public function addSettingsData( $field )
    {
      $field_group = acf_get_field_group( $field['parent'] );

      foreach ([
        self::SETTINGS_ENABLED      => false,
        self::SETTINGS_TABLE_NAME   => null
      ] as $key => $defaul_value)
      {
        $field[$key] = acf_maybe_get( $field_group, $key, $defaul_value );
      }

      return $field;
    }


    public function storeFieldsInCustomTable( $post_id )
    {
      global $wpdb;
      $values = [];

      foreach ( $_POST['acf'] as $key => $value )
      {
        $field = get_field_object( sanitize_key( $key ) );

        if (
          $field[self::SETTINGS_ENABLED] && $field['name'] &&
          $this->isFieldSupported($field)
        ) {
          $values[$field[self::SETTINGS_TABLE_NAME]][$this->sanitizeColumnName($field['name'])] = $this->sanitizeInput($value, $field);
        }
      }

      foreach ( $values as $table_name => $data)
      {
        $data['post_id'] = $post_id;

        $wpdb->suppress_errors = true;
        $wpdb->show_errors = false;

        if ( false  === $wpdb->replace($this->getTableName($table_name), $data ) )
        {
          $message = __('ACF: Fields in Custom Table error:', 'acffict').$wpdb->last_error;
          $this->addAdminNotice($message, 'error');
        }
      }
    }

    public function deleteFieldsInCustomTable($post_id)
    {
      global $wpdb;

      foreach ( acf_get_field_groups( ['post_id' => $post_id] ) as $field_group )
      {
        if (
          array_key_exists(self::SETTINGS_TABLE_NAME, $field_group) &&
          $field_group[self::SETTINGS_TABLE_NAME]
        ) {
          $wpdb->delete(
            $this->getTableName($field_group[self::SETTINGS_TABLE_NAME]),
            ['post_id' => $post_id]
          );
        }
      }
    }

    #@todo: Restrict to only page, post and custom_post_type
    public function createOrUpdateTable( $field_group )
    {
      if ( !$field_group[self::SETTINGS_ENABLED]) {
        return;
      }

      $columns  = [];
      $fields   = acf_get_fields( $field_group );

      foreach ( $fields as $field )
      {
        if ( false !== ( $column = $this->getColumnDefinition( $field ) ) ) {
          $columns[$this->sanitizeColumnName($field['name'])] = $column;
        }
      }

      $response = $this->doCreateOrAlterTable(
        $this->getTableName( $field_group[self::SETTINGS_TABLE_NAME] ),
        $columns
      );

      if ( $response !== true ) {
        $message = __('ACF: Fields in Custom Table error:', 'acffict').$response;
        $this->addAdminNotice($message, 'error');
      }
    }

    public function loadFieldFromCustomTable( $value, $post_id, $field )
    {
      $table_name = $this->getTableName( $field[self::SETTINGS_TABLE_NAME] );
      if (
        array_key_exists(self::SETTINGS_ENABLED, $field) &&
        $field[self::SETTINGS_ENABLED] &&
        $this->tableExists( $table_name )
      )
      {
        global $wpdb;

        $column_name = sanitize_key($field['name']);

        $value = $wpdb->get_var( $wpdb->prepare(
          "SELECT $column_name FROM $table_name WHERE post_id = %d", $post_id
        ));

        return $value;
      }

      return $value;
    }

    public function validateFieldGroup( $field_group )
    {
      if ( array_key_exists( self::SETTINGS_TABLE_NAME, $field_group ) ) {
        $field_group[self::SETTINGS_TABLE_NAME] = $this->sanitizeTableName( $field_group[self::SETTINGS_TABLE_NAME] );
      }
      return $field_group;
    }

    private function doCreateOrAlterTable($table_name, $columns)
    {
      global $wpdb;

      $wpdb->suppress_errors = true;
      $wpdb->show_errors = false;

      $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

      if ( $wpdb->get_var( $query ) === $table_name )
      {
        $results = $wpdb->get_results('SHOW COLUMNS FROM '.$table_name);

        $missing_columns = array_diff(
          array_keys($columns),
          array_column($results, 'Field')
        );

        foreach ($missing_columns as $column_key)
        {
          if ( array_key_exists($column_key, $columns)) {
            $wpdb->query("ALTER TABLE ".$table_name." ADD ".$columns[$column_key]." COMMENT 'Added at ".date('Y-m-d H:i:s')."' ");
            if ( $wpdb->last_error ) {
              return $wpdb->last_error;
            }
          }
        }

        $results = $wpdb->get_results('SHOW COLUMNS FROM '.$table_name);
        $missing_columns = array_diff(
          array_keys($columns),
          array_column($results, 'Field')
        );

        return count($missing_columns) == 0 ? true : __('Unable to alter table', 'acffict');
      } else
      {
        $create_ddl = "CREATE TABLE IF NOT EXISTS $table_name (
          post_id bigint(20) unsigned NOT NULL,
          ".join(",\n\t", $columns).(count($columns) > 0 ? ',' : '')."
          PRIMARY KEY (post_id)
        ) ENGINE=InnoDB {$wpdb->get_charset_collate()};";

        $wpdb->query( $create_ddl );

        if ( $wpdb->last_error ) {
          return $wpdb->last_error;
        }

        if ( $wpdb->get_var( $query ) === $table_name ) {
          return true;
        }

        return false;
      }
    }

    private function sanitizeInput( $value, $field )
    {
      $sanitized_value = null;

      switch ( $field['type'] )
      {
        case 'text':
        case 'image':
        case 'email':
        case 'url':
        case 'password':
        case 'select':
        case 'color_picker':
        case 'date_picker':
          $sanitized_value = sanitize_text_field( $value );
          break;
        case 'wysiwyg':
          $sanitized_value = wp_kses_post( $value );
          break;
        case 'textarea':
          $sanitized_value = sanitize_textarea_field( $value );
          break;
        case 'true_false':
        case 'number':
          $sanitized_value = filter_var( $value, FILTER_SANITIZE_NUMBER_INT );
          break;
        default:
          $sanitized_value = '';
      }

      return $sanitized_value;
    }

    private function sanitizeTableName($value) {
      return str_replace( '-','_', sanitize_key( $value ) );
    }
    private function sanitizeColumnName($value) {
      return $this->sanitizeTableName($value);
    }

    private function getColumnDefinition( $field )
    {
      $column_type = '';
      switch ( $field['type'] )
      {
        case 'text':
        case 'image':
        case 'email':
        case 'url':
        case 'password':
        case 'select':
          $column_type = 'VARCHAR(255)';
          break;
        case 'color_picker':
          $column_type = 'VARCHAR(7)';
          break;
        case 'number':
          $column_type = 'NUMERIC';
          break;
        case 'wysiwyg':
        case 'textarea':
          $column_type = 'LONGTEXT';
          break;
        case 'date_picker':
          $column_type = 'DATE';
          break;
        case 'true_false':
          $column_type = 'TINYINT(1)';
          break;
        default:
          $column_type = false;
      }

      $column_definition = $column_type
        ? sprintf('%s %s %s',
          $field['name'],
          $column_type,
          ($field['required'] ? 'NOT NULL' : 'NULL')
        )
        : false
      ;

      return $column_definition;
    }

    private function getTableName($name = '') {
      global $wpdb;
      return sprintf('%s%s%s', $wpdb->prefix, 'acf_', $name);
    }

    private function isFieldSupported($field) {
      return $this->getColumnDefinition($field) !== false;
    }

    private function tableExists($table_name)
    {
      global $wpdb;
      $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );
      return $wpdb->get_var( $query ) === $table_name;
    }

    private function addAdminNotice($message, $status) {
      set_transient('acffict_notice_' . get_current_user_id(), [
        'message' => $message,
        'status' => $status
      ], 30);
    }

    private function getAdminNotice() {
      $key = 'acffict_notice_' . get_current_user_id();
      $transient = get_transient( $key );
      if ( $transient ) {
          delete_transient( $key );
      }
      return $transient;
    }
  }

  ACF_FICT::getInstance();
}

