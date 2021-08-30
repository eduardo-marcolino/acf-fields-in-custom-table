<?php

class ACF_FICT_Field_Textarea extends ACF_FICT_Field
{
    public $name = 'textarea';
    public $column_type = 'longtext';

    public function sanitize($value, $acf_field)
    {
        return sanitize_textarea_field($value);
    }

    public function escape($value, $acf_field)
    {
        return esc_html($value);
    }
}

new ACF_FICT_Field_Textarea();
