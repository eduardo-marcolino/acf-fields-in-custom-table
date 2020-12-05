<?php

class ACF_FICT_Admin_Notices
{
  public static function add( $message, $status ) {
    set_transient('acfict_notice_' . get_current_user_id(), [
      'message' => $message,
      'status' => $status
    ], 30);
  }

  public static function get() {
    $key = 'acfict_notice_' . get_current_user_id();
    $transient = get_transient( $key );
    if ( $transient ) {
        delete_transient( $key );
    }
    return $transient;
  }
}

?>
