<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class InCuca_Endpoints {
    public static function register_endpoints() {
        register_rest_route('incuca/v1', '/wordfence', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_wordfence_summary'),
            'permission_callback' => array('InCuca_Permissions', 'check_api_key')
        ));
        register_rest_route('incuca/v1', '/logs', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_logs'),
            'permission_callback' => array('InCuca_Permissions', 'check_api_key')
        ));
        register_rest_route('incuca/v1', '/system-info', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_system_info'),
            'permission_callback' => array('InCuca_Permissions', 'check_api_key')
        ));
        register_rest_route('incuca/v1', '/post', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'create_post'),
            'permission_callback' => array('InCuca_Permissions', 'check_api_key')
        ));
    }

    public static function get_wordfence_summary() {
        require_once INCUCA_PLUGIN_DIR . 'includes/class-incuca-wordfence.php';
        return InCuca_Wordfence::get_wordfence_summary();
    }

    public static function get_logs() {
        require_once INCUCA_PLUGIN_DIR . 'includes/class-incuca-wordfence.php';
        return InCuca_Wordfence::get_logs();
    }

    public static function get_system_info() {
        require_once INCUCA_PLUGIN_DIR . 'includes/class-incuca-info.php';
        return InCuca_Info::get_system_info();
    }
    
    public static function create_post() {
        if ($json = json_decode(file_get_contents('php://input', true), true)) {
            foreach ($json as $id => $value) {
                if (!isset($_POST[$id])) {
                    $_POST[$id] = $value;
                }
            }
        }
        require_once INCUCA_PLUGIN_DIR . 'includes/class-incuca-posts.php';
        return InCuca_Posts::create_post();
    }
}
?>
