<?php

abstract class ACF_FICT_Field
{
  function __construct()
  {
    add_filter( 'acfict_supports_'.$this->type(), '__return_true' );
    add_filter( 'acfict_column_type_'.$this->type(), [ $this, 'column_definition'] );
    add_filter( 'acfict_sanitize_'.$this->type(), [ $this, 'sanitize'], 10, 2 );
    add_filter( 'acfict_escape_'.$this->type(), [ $this, 'escape'], 10, 2 );
  }

  public function type() {
    return $this->name;
  }

  public function column_type($acf_field) {
    return $this->column_type;
  }

  public function column_definition($acf_field)
  {
    return sprintf( '%s %s %s',
      acfict_sanitize_keyword( $acf_field['name'] ),
      $this->column_type( $acf_field ),
      $acf_field['required'] ? 'NOT NULL' : 'NULL'
    );
  }

  abstract public function sanitize($value, $acf_field);
  abstract public function escape($value, $acf_field);
}
