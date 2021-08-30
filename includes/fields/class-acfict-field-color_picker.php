<?php

class ACF_FICT_Field_Color_Picker extends ACF_FICT_Field
{
    public function type()
    {
        return 'color_picker';
    }

    public function sanitize($value, $acf_field)
    {
        return sanitize_text_field($value);
    }

    public function escape($value, $acf_field)
    {
        return esc_attr($value);
    }

    public function column_type($acf_field)
    {
        return 'varchar(7)';
    }
}

new ACF_FICT_Field_Color_Picker();
