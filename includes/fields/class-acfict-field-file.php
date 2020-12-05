<?php

class ACF_FICT_Field_File extends ACF_FICT_Field
{
  public function type( ) {
    return 'file';
  }

  public function sanitize( $value, $acf_field ) {
    return filter_var( $value, FILTER_SANITIZE_NUMBER_INT );
  }

  public function escape( $value, $acf_field ) {
    return esc_attr( $value );
  }

  public function column_type($acf_field) {
    return 'bigint(20) unsigned';
  }
}

new ACF_FICT_Field_File();
