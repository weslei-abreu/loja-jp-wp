<?php

if (! function_exists('zota_add_language_to_storage_key')) {
    function zota_add_language_to_storage_key( $storage_key )
    {
      global $sitepress;

      return $storage_key . '-' . $sitepress->get_current_language();
    }
}
add_filter( 'zota_storage_key', 'zota_add_language_to_storage_key', 10, 1 );