<?php

/**
 * acfict_include
 *
 * Includes a file within the ACF Fields in Custom Table plugin.
 *
 * @date	2020-12-05
 * @since	0.3
 *
 * @param	string $filename The specified file.
 * @return	void
 */

function acfict_include( $filepath ) {
  include_once( plugin_dir_path( ACF_FICT_PLUGIN_FILE ).$filepath );
}

/**
 * acfict_sanitize_keyword
 *
 * Sanitize database keywords values
 *
 * @date	2020-12-05
 * @since	0.3
 *
 * @param	string $string
 * @return string
 */

function acfict_sanitize_keyword( $value ) {
  return str_replace( '-','_', sanitize_key( $value ) );
}

/**
 * acfict_admin_notice_add
 *
 * Add an admin notice
 *
 * @date	2020-12-05
 * @since	0.3
 *
 * @param	string $message
 * @param	string $status error|success|info
 * @return void
 */

function acfict_admin_notice_add( $message, $status ) {
  set_transient('acfict_notice_' . get_current_user_id(), [
    'message' => $message,
    'status' => $status
  ], 30);
}

/**
 * acfict_admin_notice_get
 *
 * Returns the latest admin notice if there is one
 *
 * @date	2020-12-05
 * @since	0.3
 *
 * @return string|bool
 */

function acfict_admin_notice_get()
{
  $key = 'acfict_notice_' . get_current_user_id();
  $transient = get_transient( $key );
  if ( $transient ) {
      delete_transient( $key );
  }
  return $transient;
}

add_action('init', function()
{
  register_post_type('healthcare', [
    'labels'      => [
      'name'          => 'Plano de Saúde',
      'singular_name' => 'Planos de Saúde'
    ],
    'public'      => true,
    'has_archive' => true,
  ]);
});
