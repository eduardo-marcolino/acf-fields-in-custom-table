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

?>
