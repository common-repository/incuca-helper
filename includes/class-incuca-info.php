<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class InCuca_Info
{
    public static function get_system_info()
    {
        // Obter informações dos plugins
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = get_plugins();
        $active_plugins = get_option('active_plugins');
        $plugins_info = array();

        foreach ($plugins as $file_path => $plugin_data) {
            $plugin_data = InCuca_Helpers::incuca_get_plugin_data($plugin_data, $file_path, $active_plugins);
            $plugins_info[$plugin_data['slug']] = $plugin_data;
        }

        // Obter informações do tema ativo
        $theme = wp_get_theme();
        $theme_info = array(
            'name' => $theme->get('Name'),
            'version' => $theme->get('Version'),
            'slug' => get_template(),
        );

        // Obter a versão do core do WordPress
        $wp_version = get_bloginfo('version');

        // Initialize an array to hold the web server information.
        $webserver = array(
            'id'      => null,
            'name'    => null,
            'version' => null,
        );

        if (isset($_SERVER['SERVER_SOFTWARE'])) {
            $webserver_software = trim(wp_kses(wp_unslash($_SERVER['SERVER_SOFTWARE']), 'strip'));

            if (preg_match('/^(\w+)\/?([^\s]*)/', $webserver_software, $matches)) {
                if (isset($matches[1]) && trim((string) $matches[1])) {
                    $webserver['name'] = trim($matches[1]);
                }

                if (isset($matches[2]) && trim((string) $matches[2])) {
                    $webserver['version'] = trim($matches[2]);
                }
            }
        }

        if (isset($webserver['name']) && $webserver['name']) {
            switch (trim(strtolower($webserver['name']))) {
                case 'apache':
                    $webserver['id']   = 'apache';
                    $webserver['name'] = 'Apache HTTPD';
                    break;
                case 'nginx':
                    $webserver['id']   = 'nginx';
                    $webserver['name'] = 'nginx';
                    break;
            }
        }

        if (isset($webserver['version']) && $webserver['version']) {

            $version = InCuca_Helpers::incuca_sanitize_version($webserver['version']);

            if (preg_match('/^(\d+\.\d+(\.\d+)?)/', $version, $match)) {
                if (isset($match[0])) {
                    $webserver['version'] = trim($match[0]);
                }
            }
        }

        // Get the PHP version.
        $php_version = phpversion();

        if (isset($php_version) && $php_version) {
            $php_version =  InCuca_Helpers::incuca_sanitize_version($php_version);

            if (preg_match('/^(\d+\.\d+(\.\d+)?)/', $php_version, $match)) {
                if (isset($match[0])) {
                    $php_version = trim($match[0]);
                }
            }
        }

        // Construir a resposta
        $data = array(
            'core_version' => $wp_version,
            'theme' => $theme_info,
            'plugins' => $plugins_info,
            'webserver' => $webserver,
            'php' => $php_version,
        );

        return rest_ensure_response($data);
    }
}
