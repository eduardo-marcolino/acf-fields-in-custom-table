<?php

class ACF_FICT_Field_Password extends ACF_FICT_Field_Text
{
    public function type()
    {
        return 'password';
    }
}

new ACF_FICT_Field_Password();
