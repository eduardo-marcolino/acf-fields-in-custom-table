<?php

class ACF_FICT_Field_Radio extends ACF_FICT_Field_Text
{
    public function type()
    {
        return 'radio';
    }
}

new ACF_FICT_Field_Radio();
