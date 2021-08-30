<?php

class ACF_FICT_Field_Image extends ACF_FICT_Field_File
{
    public function type()
    {
        return 'image';
    }
}

new ACF_FICT_Field_Image();
