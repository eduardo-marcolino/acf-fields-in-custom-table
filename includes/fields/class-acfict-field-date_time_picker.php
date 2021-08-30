<?php

class ACF_FICT_Field_Date_Time_Picker extends ACF_FICT_Field
{
    public function type()
    {
        return 'date_time_picker';
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
        return 'datetime';
    }
}

new ACF_FICT_Field_Date_Time_Picker();
