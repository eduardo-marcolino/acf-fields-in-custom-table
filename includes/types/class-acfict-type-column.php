<?php

class ACF_FICT_Type_Column extends ACF_FICT_Type
{
    public $column_name,
        $column_type
    ;

    public function __construct($column_name, $column_type)
    {
        $this->column_name = $column_name;
        $this->column_type = $column_type;
    }

    public function get_definition()
    {
        return sprintf('%s %s',
            $this->column_name,
            $this->column_type
        );
    }
}
