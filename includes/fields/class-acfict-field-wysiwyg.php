<?php

class ACF_FICT_Field_Wysiwyg extends ACF_FICT_Field
{
    public $name = 'wysiwyg';
    public $column_type = 'longtext';

    public function sanitize($value, $acf_field)
    {
        return wp_kses_post($value);
    }

    public function escape($value, $acf_field)
    {
        return $value;
    }
}

new ACF_FICT_Field_Wysiwyg();
