<?php

class ACF_FICT_Field_Number extends ACF_FICT_Field
{
    public function type()
    {
        return 'number';
    }

    public function sanitize($value, $acf_field)
    {
        return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    public function escape($value, $acf_field)
    {
        return esc_attr($value);
    }

    public function column_type($acf_field)
    {
        return 'float';
    }
}

new ACF_FICT_Field_Number();
