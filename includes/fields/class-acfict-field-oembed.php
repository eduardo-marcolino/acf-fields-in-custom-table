<?php

class ACF_FICT_Field_Oembed extends ACF_FICT_Field_Url
{
    public function type()
    {
        return 'oembed';
    }
}

new ACF_FICT_Field_Oembed();
