<?php
/*
Plugin Name: ACF: Fields in Custom Table
Description: Stores ACF custom fields in a custom table instead of WordPress' core meta tables.
Version: 0.1
Author: Eduardo Marcolino
Author URI: https://eduardomarcolino.com
Text Domain: acffict
Domain Path: /languages
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
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
      add_action( 'acf/render_field_group_settings', [$this, 'registerSettings'] );
      add_action( 'acf/update_field_group', [$this, 'createOrUpdateTable'] );
      add_action( 'acf/save_post', [$this, 'storeFieldsInCustomTable'], 1 );
      add_action( 'delete_post', [$this, 'deleteFieldsInCustomTable'] );

      add_filter( 'acf/load_field', [$this, 'addSettingsData'] );
      add_filter( 'acf/load_value', [$this, 'loadFieldFromCustomTable'], 11, 3 );

      load_plugin_textdomain( 'acffict', false, dirname( plugin_basename( ACF_FICT_PLUGIN_FILE ) ) . '/languages' );
    }

    public function registerSettings( $field_group )
    {
      acf_render_field_wrap( [
        'label'			    => __('ACF: Fields in Custom Table', 'acffict'),
        'instructions'	=> __( 'Enable ACF: Fields in Custom Table for this field group?', 'acffict' ),
        'type'			    => 'true_false',
        'name'			    => self::SETTINGS_ENABLED,
        'key'           => self::SETTINGS_ENABLED,
        'prefix'		    => 'acf_field_group',
        'value'         => acf_maybe_get( $field_group, self::SETTINGS_ENABLED, false ),
        'ui'			      => 1,
      ] );

      acf_render_field_wrap( [
        'label'			=> _( 'Custom table name', 'acffict' ),
        'instructions'	=> __( 'Define the custom table name. Make sure it doesn\'t conflict with others tables names.', 'acffict' ),
        'type'			=> 'text',
        'name'			=> self::SETTINGS_TABLE_NAME,
        'prefix'		=> 'acf_field_group',
        'value'			=> acf_maybe_get( $field_group, self::SETTINGS_TABLE_NAME, false ),
        'prepend'   => $this->getTableName(),
        'required'  => true,
        'conditional_logic' => [
          'field' => self::SETTINGS_ENABLED,
          'operator' => '==',
          'value' => '1',
        ]
      ] );
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

    #@todo: Improve security by check table name and column
    public function storeFieldsInCustomTable( $post_id )
    {
      global $wpdb;
      $values = [];

      foreach ( $_POST['acf'] as $key => $value )
      {
        $field = get_field_object( $key );

        if (
          $field[self::SETTINGS_ENABLED] && $field['name'] &&
          $this->isFieldSupported($field)
        ) {
          $values[$field[self::SETTINGS_TABLE_NAME]][$field['name']] = $value;
        }
      }

      foreach ( $values as $table_name => $data)
      {
        $data['post_id'] = $post_id;
        if ( false  === $wpdb->replace($this->getTableName($table_name), $data ) ) {
          throw new Exception('Insert error');
        }
      }
    }

    #@todo: Improve security by check table name and column
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
          $columns[$field['name']] = $column;
        }
      }

      $success = $this->doCreateOrAlterTable(
        $this->getTableName( $field_group[self::SETTINGS_TABLE_NAME] ),
        $columns
      );

      if ( !$success ) throw new Exception( 'Table creation error' );
    }

    #@todo: Improve security by check table name and column
    public function loadFieldFromCustomTable( $value, $post_id, $field )
    {
      if ($field[self::SETTINGS_ENABLED])
      {
        global $wpdb;

        $column_name = esc_sql($field['name']);
        $table_name = esc_sql($this->getTableName($field[self::SETTINGS_TABLE_NAME]));

        $value = $wpdb->get_var( $wpdb->prepare(
          "SELECT $column_name FROM $table_name WHERE post_id = %d", $post_id
        ));

        return $value;
      }

      return $value;
    }

    #@todo: Improve security by check table name and column
    private function doCreateOrAlterTable($table_name, $columns)
    {
      global $wpdb;
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
          }
        }

        $results = $wpdb->get_results('SHOW COLUMNS FROM '.$table_name);
        $missing_columns = array_diff(
          array_keys($columns),
          array_column($results, 'Field')
        );

        return count($missing_columns) == 0;
      } else
      {
        $create_ddl = "CREATE TABLE IF NOT EXISTS $table_name (
          post_id bigint(20) unsigned NOT NULL,
          ".join(",\n\t", $columns).(count($columns) > 0 ? ',' : '')."
          PRIMARY KEY (post_id)
        ) ENGINE=InnoDB {$wpdb->get_charset_collate()};";

        $wpdb->query( $create_ddl );
        if ( $wpdb->get_var( $query ) === $table_name ) {
          return true;
        }

        return false;
      }
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
      return sprintf('%s%s%s', $wpdb->prefix, 'fict_', $name);
    }

    private function isFieldSupported($field) {
      return $this->getColumnDefinition($field) !== false;
    }
  }

  ACF_FICT::getInstance();
}

