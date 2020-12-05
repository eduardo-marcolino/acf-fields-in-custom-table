<?php

/**
 * @TODO: If field allows multiple options then
 * decide if it's going to store as json or on a different table.
 * It it's single option, then the type should be varchar
 */
class ACF_FICT_Field_User extends ACF_FICT_Field_Relationship
{
  public function type( ) {
    return 'user';
  }
}

new ACF_FICT_Field_User();
