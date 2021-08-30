<?php

class ACF_FICT_Field_Email extends ACF_FICT_Field_Text
{
    public function type()
    {
        return 'email';
    }
}

new ACF_FICT_Field_Email();
