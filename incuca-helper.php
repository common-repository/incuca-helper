<?php
/*
Plugin Name: InCuca Helper
Plugin URI: https://app.incuca.net/
Description: Plugin de conexão com o sistema da InCuca Tech.
Version: 1.1.0
Author: InCuca Tech
Author URI: https://incuca.net/
Text Domain: incuca-helper
Domain Path: /languages
Stable Tag: 1.1.0
Requires PHP: 7.4
Tested up to: 6.6.2
Contributors: incuca, samoaste
License: GPLv3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Definir constantes
define('INCUCA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('INCUCA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('INCUCA_PLUGIN_VERSION', '1.1.0');

// Incluir arquivos necessários
require_once INCUCA_PLUGIN_DIR . 'includes/functions.php';
require_once INCUCA_PLUGIN_DIR . 'includes/class-incuca-api.php';
require_once INCUCA_PLUGIN_DIR . 'includes/class-incuca-options-page.php';

// Inicialização do plugin
function incuca_api_init()
{
    InCuca_API::get_instance();
}
add_action('init', 'incuca_api_init');

// Redirecionar para a tela de opções após ativação
function incuca_plugin_activate()
{
    add_option('incuca_plugin_do_activation_redirect', true);
}
register_activation_hook(__FILE__, 'incuca_plugin_activate');

function incuca_plugin_redirect()
{
    if (get_option('incuca_plugin_do_activation_redirect', false)) {
        delete_option('incuca_plugin_do_activation_redirect');
        if (!isset($_GET['activate-multi']) && is_admin()) {
            wp_redirect(admin_url('options-general.php?page=incuca-conexao'));
            exit;
        }
    }
}
add_action('admin_init', 'incuca_plugin_redirect');

// Adicionar link de ação personalizada na listagem de plugins
function incuca_plugin_action_links($links)
{
    $settings_link = '<a href="options-general.php?page=incuca-conexao">' . __('Inserir chave de conexão', 'incuca-helper') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'incuca_plugin_action_links');

// Adicionar o script no site se o usuário escolher
function incuca_insert_custom_script()
{
    $incuca_api_key = get_option('incuca_api_key');
    if (isset($incuca_api_key)) {
        if ($incuca_api_key['show_script'] == 1) {
            if ($incuca_api_key['script']) {
                wp_enqueue_script('incuca-js', 'https://app.incuca.net/pixel/' . esc_attr($incuca_api_key['script']), [], null, true);
            }
        }
    }
}
add_action('wp_footer', 'incuca_insert_custom_script');
