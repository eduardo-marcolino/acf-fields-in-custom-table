<?php

class ACF_FICT_Field_Text extends ACF_FICT_Field
{
    public $name = 'text';
    public $column_type = 'varchar(255)';

    public function sanitize($value, $acf_field)
    {
        return sanitize_text_field($value);
    }

    public function escape($value, $acf_field)
    {
        return esc_attr($value);
    }
}

new ACF_FICT_Field_Text();
