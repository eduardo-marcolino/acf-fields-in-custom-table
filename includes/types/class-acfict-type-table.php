<?php

class ACF_FICT_Type_Table extends ACF_FICT_Type
{
  public
    $table_name,
    $columns = []
  ;

  function __construct($table_name, $columns)
  {
    $this->table_name = $table_name;
    $this->columns = $columns;
  }

  public function get_definition()
  {
    $columns = [];

    foreach($this->columns as $field)
    {
      $column_type = apply_filters('acfict_column_type_'.$field['type'], $field);
      if ( $column_type && is_a( $column_type, 'ACF_FICT_Type_Column') ) {
        $columns[$this->table_name][] = $column_type->get_definition();
      }

      if ( $column_type && is_a( $column_type, 'ACF_FICT_Type_Table') ) {
        $columns = array_merge($columns, $column_type->get_definition());
      }
    }

    return $columns;
  }
}
