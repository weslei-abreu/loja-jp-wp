<?php

if(!defined('ABSPATH')){
	exit();
}

$speedycache_ac_config = array (
  'settings' => 
  array (
    'status' => true,
    'gzip' => true,
    'logged_in_user' => false,
    'mobile_theme' => false,
    'mobile' => false,
  ),
  'excludes' => 
  array (
    0 => 
    array (
      'type' => 'cookie',
      'prefix' => 'contain',
      'content' => 'dokan_pro_dokan_quote_',
    ),
    1 => 
    array (
      'type' => 'cookie',
      'prefix' => 'contain',
      'content' => 'zota_recently_viewed_products_list',
    ),
    2 => 
    array (
      'type' => 'cookie',
      'prefix' => 'contain',
      'content' => 'wp_woocommerce_session_',
    ),
    3 => 
    array (
      'type' => 'page',
      'prefix' => 'contain',
      'content' => '/cart/',
    ),
    4 => 
    array (
      'type' => 'page',
      'prefix' => 'contain',
      'content' => '/checkout/',
    ),
    5 => 
    array (
      'type' => 'page',
      'prefix' => 'contain',
      'content' => '/my-account/',
    ),
  ),
);

if(empty($speedycache_ac_config) || !is_array($speedycache_ac_config)){
	$speedycache_ac_config = [];
}