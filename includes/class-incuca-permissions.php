<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class InCuca_Permissions {
    public static function check_api_key($request) {
        // Obter o cabeçalho de autorização
        $auth_header = $request->get_header('Authorization');
        if (empty($auth_header)) {
            return new WP_Error('rest_forbidden', 'Sem permissão para fazer isso.', array('status' => 401));
        }

        // Decodificar o cabeçalho base64
        list($auth_type, $auth_token) = explode(' ', $auth_header, 2);
        if ($auth_type !== 'Bearer') {
            return new WP_Error('rest_forbidden', 'Autenticação inválida.', array('status' => 401));
        }

        // Verificar a chave de API
        $stored_api_key = get_option('incuca_api_key');

        if (empty($stored_api_key)) {
            return new WP_Error('rest_forbidden', 'Plugin não configurado.', array('status' => 401));
        }

        if ($auth_token !== $stored_api_key['api_key']) {
            return new WP_Error('rest_forbidden', 'Chave de API inválida.', array('status' => 403));
        }

        return true;
    }
}
?>
