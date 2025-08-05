<?php
/**
 * Plugin Name: Corrigir Permissão REST para Vendedor ver Cupom
 * Description: Adiciona permissão especial para vendedores acessarem a rota de usuário necessária na criação de cupons no Dokan.
 */

add_filter( 'rest_user_query', function ( $args, $request ) {
    // Permite vendedores verem apenas seu próprio usuário via REST
    if ( current_user_can( 'seller' ) && isset( $request['context'] ) && $request['context'] === 'edit' ) {
        $args['include'] = [ get_current_user_id() ];
    }
    return $args;
}, 10, 2 );

add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/user/(?P<id>\d+)', [
        'methods'  => 'GET',
        'callback' => function ($request) {
            $user_id = (int) $request['id'];
            $user = get_user_by('id', $user_id);

            if (!$user) {
                return new WP_Error('not_found', 'Usuário não encontrado', ['status' => 404]);
            }

            return [
                'ID'           => $user->ID,
                'user_login'   => $user->user_login,
                'display_name' => $user->display_name,
                'user_email'   => $user->user_email,
                'roles'        => $user->roles,
            ];
        },
        'permission_callback' => function ($request) {
            // Só permite o próprio usuário ver seus dados
            return get_current_user_id() === (int) $request['id'];
        },
    ]);
});
