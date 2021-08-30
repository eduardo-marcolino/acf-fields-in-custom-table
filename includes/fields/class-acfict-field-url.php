<?php

class ACF_FICT_Field_Url extends ACF_FICT_Field_Text
{
    public function type()
    {
        return 'url';
    }

    public function sanitize($value, $acf_field)
    {
        return esc_url_raw($value);
    }
}

new ACF_FICT_Field_Url();
