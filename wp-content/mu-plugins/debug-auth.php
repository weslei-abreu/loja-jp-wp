<?php
/*
Plugin Name: Debug Authentication
Description: Debug script para entender o fluxo de autenticação
Version: 1.0
Author: Weslei
*/

// Debug do fluxo de autenticação
add_action('wp_authenticate', function($username, $password) {
    $logFile = '/tmp/debug-auth.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] [wp_authenticate] Iniciando autenticação para: $username\n", FILE_APPEND);
}, 10, 2);

// Debug do filtro authenticate
add_filter('authenticate', function($user, $username, $password) {
    $logFile = '/tmp/debug-auth.log';
    $timestamp = date('Y-m-d H:i:s');
    
    if ($user instanceof WP_User) {
        file_put_contents($logFile, "[$timestamp] [authenticate] Usuário já autenticado: {$user->user_login} (ID: {$user->ID})\n", FILE_APPEND);
    } elseif (is_wp_error($user)) {
        file_put_contents($logFile, "[$timestamp] [authenticate] Erro de autenticação: " . $user->get_error_message() . "\n", FILE_APPEND);
    } else {
        file_put_contents($logFile, "[$timestamp] [authenticate] Usuário não autenticado ainda\n", FILE_APPEND);
    }
    
    return $user;
}, 5, 3);

// Debug do wp_authenticate_username_password
add_action('wp_authenticate_username_password', function($user, $username, $password) {
    $logFile = '/tmp/debug-auth.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] [wp_authenticate_username_password] Verificando usuário: $username\n", FILE_APPEND);
}, 10, 3);

// Debug do wp_check_password
add_action('wp_check_password', function($check, $password, $hash, $user_id) {
    $logFile = '/tmp/debug-auth.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] [wp_check_password] Verificando senha para user_id: $user_id | Resultado: " . ($check ? 'true' : 'false') . "\n", FILE_APPEND);
}, 10, 4); 