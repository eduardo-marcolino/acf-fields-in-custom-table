<?php

/**
 * @TODO: If field allows multiple options then
 * decide if it's going to store as json or on a different table.
 * It it's single option, then the type should be varchar
 */
class ACF_FICT_Field_Relationship extends ACF_FICT_Field
{
  public $name = 'relationship';
  public $column_type = 'longtext';

  public function sanitize( $value, $acf_field )
  {
    if ( !is_array($value) ) {
      return filter_var( $value, FILTER_SANITIZE_NUMBER_INT );
    }

    return json_encode(array_map( function($item) {
      return filter_var( $item, FILTER_SANITIZE_NUMBER_INT );
    }, $value));
  }

  public function escape( $value, $acf_field ) {
    $object = json_decode($value, true);
    return $object ? $object : esc_attr( $value );
  }
}

new ACF_FICT_Field_Relationship();
