<?php

class ACF_FICT_Field_Repeater extends ACF_FICT_Field
{
  public $name = 'repeater';
  public $column_type = 'empty';

  public function sanitize( $value, $acf_field ) {
    return sanitize_text_field( $value );
  }

  public function escape( $value, $acf_field ) {
    return esc_attr( $value );
  }

  public function column_definition( $acf_field )
  {
    return new ACF_FICT_Type_Table($acf_field[ACF_FICT::SETTINGS_TABLE_NAME], $acf_field['sub_fields']);
  }
}

new ACF_FICT_Field_Repeater();
