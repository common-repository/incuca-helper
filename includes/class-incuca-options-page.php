<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class InCuca_Options_Page
{
    public static function init()
    {
        add_action('admin_menu', array(__CLASS__, 'add_options_page'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
        add_action('wp_ajax_incuca_check_api_key', array(__CLASS__, 'check_api_key'));
        add_action('wp_ajax_incuca_check_show_script', array(__CLASS__, 'check_show_script'));
    }

    public static function add_options_page()
    {
        add_submenu_page(
            'options-general.php',
            'InCuca conexão',
            'InCuca conexão',
            'manage_options',
            'incuca-conexao',
            array(__CLASS__, 'render_options_page')
        );
    }

    public static function enqueue_scripts($hook)
    {
        // Verificar se estamos na página de opções do InCuca
        if ($hook !== 'settings_page_incuca-conexao') {
            return;
        }
        wp_enqueue_style('bootstrap-css', INCUCA_PLUGIN_URL . 'assets/css/bootstrap.5.3.3.min.css', array(), '4.5.2');
        wp_enqueue_style('font-awesome-css', INCUCA_PLUGIN_URL . 'assets/css/fontawesome.6.5.2.all.min.css', array(), '6.5.2');
        wp_enqueue_style('incuca-custom-css', INCUCA_PLUGIN_URL . 'assets/css/incuca-custom.css', array(), INCUCA_PLUGIN_VERSION);
        wp_enqueue_script('bootstrap-js', INCUCA_PLUGIN_URL . 'assets/js/bootstrap.5.3.3.min.js', array('jquery'), INCUCA_PLUGIN_VERSION, true);
        wp_enqueue_script('incuca-custom-js', INCUCA_PLUGIN_URL . 'assets/js/incuca-custom.js', array('jquery'), INCUCA_PLUGIN_VERSION, true);
        wp_localize_script('incuca-custom-js', 'incuca_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));
    }

    public static function check_api_key()
    {
        if (!isset($_POST['api_key']) && is_admin()) {
            wp_send_json_error('API key is missing.');
        }

        $url = wp_guess_url();
        $hostname = preg_replace("(^https?://)", "", $url);
        $api_key = sanitize_key($_POST['api_key']);
        $response = wp_remote_post('https://app.incuca.net/webhook-integration', array(
            'body'    => $hostname,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
            ),
            'timeout' => 30,
        ));

        $body = wp_remote_retrieve_body($response);

        $response_code = wp_remote_retrieve_response_code($response);

        if ($response_code !== 200) {
            wp_send_json_error($body);
        }
        self::set_option('script', $body);
        wp_send_json_success($body);
        wp_die();
    }

    public static function check_show_script()
    {
        if (!isset($_POST['show_script']) && is_admin()) {
            wp_send_json_error('Faltando parâmetro.');
        }
        $show_script = rest_sanitize_boolean($_POST['show_script']);
        self::set_option('show_script', $show_script);
        wp_send_json_success($show_script);
        wp_die();
    }

    public static function render_options_page()
    {
        if (isset($_POST) && !empty($_POST['incuca_api_key']) && is_admin()) {
            $api_key = sanitize_key($_POST['incuca_api_key']);
            self::set_option('api_key', $api_key);
            echo '<div class="updated"><p>Chave de API atualizada.</p></div>';
        }
        $incuca_api_key = get_option('incuca_api_key');
        $checked = false;
        if (isset($incuca_api_key['show_script'])) {
            $checked = $incuca_api_key['show_script'];
        }
?>
        <div class="wrap" style="background-color: #f5f8ff; padding: 20px; border-radius: 8px;">
            <h1 style="color: #31363e;">Bem-vindo(a) a tela de conexão</h1>
            <p style="color: #31363e;">Esta tela permite a conexão do seu sistema com a plataforma InCuca. Por favor, insira sua chave de API para estabelecer a conexão.</p>
            <div class="card">
                <form method="post" action="">
                    <div class="form-group">
                        <label for="incuca_api_key" style="color: #31363e;">Chave de API:</label>
                        <div class="input-group">
                            <input type="password" id="incuca_api_key" name="incuca_api_key" class="form-control" value="<?php echo $incuca_api_key ? esc_attr($incuca_api_key['api_key']) : ''; ?>" />
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="toggle-password"><i class="fa fa-eye"></i></button>
                            </div>
                        </div>
                    </div>
                    <input type="submit" value="Salvar" class="btn btn-primary" style="background-color: #6366F1; border-color: #6366F1; color: #ffffff;" />
                </form>
                <p style="color: #31363e; margin-top: 20px;">Você pode encontrar sua chave de API na sua <a href="https://app.incuca.net/account#integration-key" target="_blank">conta InCuca</a>.</p>
                <div id="api-key-status" class="alert" style="display: none;"></div>
            </div>
            <div id="show_script_div" class="card" style="opacity: 0; transition: 0.2s;">
                <div class="d-flex justify-content-between align-items-center">
                    <label for="incuca_insert_script" style="color: #31363e;" class="form-check-label mb-0">Inserir script de acompanhamento</label>
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="incuca_insert_script" name="incuca_insert_script" value="1" <?php checked(1, $checked, true); ?>>
                    </div>
                </div>
                <div id="show-script-status" class="alert" style="display: none;opacity:1;transition:0.2s"></div>
            </div>
        </div>
        <?php if (!empty($incuca_api_key)) : ?>
            <div id="api_key"><?php echo esc_html($incuca_api_key['api_key']); ?></div>
            <?php wp_enqueue_script('incuca-admin-js', INCUCA_PLUGIN_URL . 'assets/js/incuca-admin.js', array('jquery'), INCUCA_PLUGIN_VERSION, true); ?>
        <?php endif; ?>
<?php
    }

    public static function set_option($option, $value)
    {
        if (current_user_can('administrator') && is_admin()) {
            $incuca_api_key = get_option('incuca_api_key');
            if (!is_array($incuca_api_key)) $incuca_api_key = [];
            $incuca_api_key[$option] = $value;
            update_option('incuca_api_key', $incuca_api_key);
        }
    }
}

InCuca_Options_Page::init();
?>