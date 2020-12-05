<?php

class ACF_FICT_Field_Range extends ACF_FICT_Field_Number
{
  public function type( ) {
    return 'range';
  }
}

new ACF_FICT_Field_Range();
