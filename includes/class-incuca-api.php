<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class InCuca_API {
    private static $instance = null;

    private function __construct() {
        // Registrar os endpoints da API
        add_action('rest_api_init', array($this, 'register_api_endpoints'));
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register_api_endpoints() {
        require_once INCUCA_PLUGIN_DIR . 'includes/class-incuca-endpoints.php';
        InCuca_Endpoints::register_endpoints();
    }
}
?>
