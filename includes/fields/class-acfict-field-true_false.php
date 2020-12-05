<?php

class ACF_FICT_Field_True_False extends ACF_FICT_Field
{
  public function type( ) {
    return 'true_false';
  }

  public function sanitize( $value, $acf_field ) {
    return filter_var( $value, FILTER_SANITIZE_NUMBER_INT );
  }

  public function escape( $value, $acf_field ) {
    return esc_attr( $value );
  }

  /*@TODO: CHeck default value*/
  public function column_type($acf_field) {
    return 'tinyint(1)';
  }
}

new ACF_FICT_Field_True_False();
