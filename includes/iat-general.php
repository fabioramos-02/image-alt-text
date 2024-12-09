<?php

defined('ABSPATH') or die('Plugin file cannot be accessed directly.');

class iat_general
{

    public $conn;
    public $wp_posts;
    public $wp_postmeta;

    public function __construct()
    {
        global $wpdb;
        $this->conn = $wpdb;
        $this->wp_posts = $this->conn->prefix . 'posts';
        $this->wp_postmeta = $this->conn->prefix . 'postmeta';
        /* admin menu */
        add_action('admin_menu', [$this, 'fn_iat_add_admin_menu_page']);
        /* remove sub menu Page */
        add_action('admin_head', [$this, 'fn_iat_remove_sub_menu_page']);
        /* remind me later */
        add_action('wp_ajax_iat_remind_me_later', [$this, 'fn_iat_remind_me_later']);
        /* do not show again */
        add_action('wp_ajax_iat_do_not_show_again', [$this, 'fn_iat_do_not_show_again']);
        /* admin notice for Review */
        add_action('admin_notices', [$this, 'iat_admin_notices']);
        /* admin scripts */
        add_action('admin_enqueue_scripts', [$this, 'iat_admin_scripts']);
        // adicionar ação para gerar o alt text
        add_action('wp_ajax_iat_generate_alt_text', 'iat_generate_alt_text_callback');

        add_action('wp_ajax_iat_generate_bulk_alt_text', 'iat_generate_bulk_alt_text_callback');
    }

    public function iat_generate_bulk_alt_text_callback()
    {
        // Verifica se a requisição é válida
        if (!isset($_POST['image_urls']) || !isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'iat_image_alt_text')) {
            wp_send_json_error(['message' => 'Erro de segurança ou URLs não fornecidas']);
            wp_die();
        }

        // Pega o array de URLs das imagens
        $image_urls = $_POST['image_urls'];
        $alt_texts = []; // Array para armazenar os textos alternativos gerados

        // Itera sobre as URLs das imagens e gera o texto alternativo
        foreach ($image_urls as $image_url) {
            $alt_text = generate_alt_text_from_openai($image_url);
            $alt_texts[] = [
                'image_url' => $image_url,
                'alt_text' => $alt_text
            ];
        }

        // Retorna os textos alternativos gerados
        wp_send_json_success(['alt_texts' => $alt_texts]);

        wp_die();
    }
    public function fn_iat_add_admin_menu_page()
    {
        $menus = [
            'main' => [
                'page_title' => __('Image Alt Text', IMAGE_ALT_TEXT),
                'menu_title' => __('Image Alt Text', IMAGE_ALT_TEXT),
                'capability' => 'manage_options',
                'menu_slug'  => 'with-alt',
                'function'   => [$this, 'fn_iat_image_alternative_text_handler'],
                'icon_url'   => 'dashicons-format-image'
            ],
            'sub' => [
                [
                    'page_title' => __('With Alt', IMAGE_ALT_TEXT),
                    'menu_title' => __('With Alt', IMAGE_ALT_TEXT),
                    'menu_slug'  => 'with-alt',
                    'function'   => [$this, 'fn_iat_with_alt_handler']
                ],
                [
                    'page_title' => __('Without Alt', IMAGE_ALT_TEXT),
                    'menu_title' => __('Without Alt', IMAGE_ALT_TEXT),
                    'menu_slug'  => 'without-alt',
                    'function'   => [$this, 'fn_iat_without_alt_handler']
                ],
                [
                    'page_title' => __('Pro Coming Soon', IMAGE_ALT_TEXT),
                    'menu_title' => __('Pro Coming Soon', IMAGE_ALT_TEXT),
                    'menu_slug'  => 'pro-coming-soon',
                    'function'   => [$this, 'fn_iat_pro_alt_handler']
                ],
            ]
        ];

        $image_alt_text = add_menu_page(
            $menus['main']['page_title'],
            $menus['main']['menu_title'],
            $menus['main']['capability'],
            $menus['main']['menu_slug'],
            $menus['main']['function'],
            $menus['main']['icon_url'],
            11
        );
        $this->fn_iat_admin_assets($image_alt_text);

        foreach ($menus['sub'] as $submenu) {
            $hook_suffix = add_submenu_page(
                'with-alt',
                $submenu['page_title'],
                $submenu['menu_title'],
                'manage_options',
                $submenu['menu_slug'],
                $submenu['function']
            );
            $this->fn_iat_admin_assets($hook_suffix);
        }
    }


    public function fn_iat_remove_sub_menu_page()
    {
        remove_submenu_page('with-alt', 'with-alt');
        remove_submenu_page('with-alt', 'without-alt');
        remove_submenu_page('with-alt', 'pro-coming-soon');
    }

    public function fn_iat_admin_assets($hook_suffix)
    {
        add_action('admin_print_styles-' . $hook_suffix, array($this, 'fn_iat_css'));
        add_action('admin_print_scripts-' . $hook_suffix, array($this, 'fn_iat_js'));
    }

    public function fn_iat_css()
    {
        $styles = [
            'iat-bootstrap-css' => '/assets/css/bootstrap.min.css',
            'iat-datatable-css' => '/assets/css/datatable.min.css',
            'iat-toastr-css' => '/assets/css/toastr.min.css',
            'iat-admin-css' => '/assets/css/iat-admin.css',
        ];

        foreach ($styles as $handle => $path) {
            wp_register_style($handle, plugins_url($path, dirname(__FILE__)), false, IAT_FILE_VERSION, 'all');
            wp_enqueue_style($handle);
        }
    }

    public function fn_iat_js()
    {
        $scripts = [
            'iat-bootstrap-js' => '/assets/js/bootstrap.min.js',
            'iat-bootstrap-bundle-js' => '/assets/js/bootstrap.bundle.min.js',
            'iat-datatable-js' => '/assets/js/datatable.min.js',
            'iat-toastr-js' => '/assets/js/toastr.min.js',
            'iat-admin-js' => '/assets/js/iat-admin.js',
        ];

        foreach ($scripts as $handle => $path) {
            wp_register_script($handle, plugins_url($path, dirname(__FILE__)), ['jquery'], IAT_FILE_VERSION, true);
            wp_enqueue_script($handle);
        }

        /* localize script for iat-admin-js */
        wp_localize_script('iat-admin-js', 'iatObj', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('iat_image_alt_text'),  // Definindo o nonce
            'msg1' => esc_html(__('Are you sure you want to copy the alt text? If "With Alt" is selected, this action will update existing alt text with the post name. If "Without Alt" is selected, it will fill all missing alt text for media items using the corresponding post name.', IMAGE_ALT_TEXT)),
            'msg2' => esc_html(__('Are you sure you want to copy the alt text? If "With Alt" is selected, this action will update attached post name to alt text . If "Without Alt" is selected, it will fill all missing alt text for media items using the corresponding attached post name.', IMAGE_ALT_TEXT)),
            'msg3' => esc_html(__('Great, All your images have alt text, Any images without alt text will appear here.', IMAGE_ALT_TEXT))
        ]);
    }


    public function fn_iat_image_alternative_text_handler()
    {
        /* Image Alt Text view (default: With Alt) */
        $file_path = IAT_FILE_PATH . '/view/iat-admin-view-header.php';
        if (file_exists($file_path)) {
            include_once $file_path;
        }
    }

    public function fn_iat_with_alt_handler()
    {

        /* With Alt view  */
        $file_path = IAT_FILE_PATH . '/view/iat-admin-view-with-alt.php';
        if (file_exists($file_path)) {
            include_once $file_path;
        }
    }

    public function fn_iat_without_alt_handler()
    {

        /* Withot Alt view  */
        $file_path = IAT_FILE_PATH . '/view/iat-admin-view-without-alt.php';
        if (file_exists($file_path)) {
            include_once $file_path;
        }
    }

    public function fn_iat_pro_alt_handler()
    {
        /* Pro Coming Soon view  */
        $file_path = IAT_FILE_PATH . '/view/iat-admin-view-pro-coming-soon.php';
        if (file_exists($file_path)) {
            include_once $file_path;
        }
    }

    public function fn_iat_remind_me_later()
    {

        $output = array();

        if (isset($_POST['action']) && $_POST['action'] == 'iat_remind_me_later') {

            $current_date = date('Y-m-d');
            $date = strtotime("+15 day", strtotime($current_date));
            $increment_date = strtotime(date('Y-m-d', $date));
            if ($increment_date) {
                $updated = update_option('iat_review_reminder', $increment_date);
                if ($updated) {
                    $flg = 1;
                    $output = array(
                        'flg' => $flg
                    );
                } else {
                    $flg = 0;
                    $message = __('Something is wrong', IMAGE_ALT_TEXT);
                    $output = array(
                        'flg' => $flg,
                        'message' => $message
                    );
                }
            }
        }
        echo json_encode($output);
        wp_die();
    }

    public function fn_iat_do_not_show_again()
    {

        $output = array();
        if (isset($_POST['action']) && $_POST['action'] == 'iat_do_not_show_again') {
            $updated = update_option('iat_do_not_show_again', 'yes');
            if ($updated) {
                $flg = 1;
                $output = array(
                    'flg' => $flg
                );
            } else {
                $flg = 0;
                $message = __('Something is wrong', IMAGE_ALT_TEXT);
                $output = array(
                    'flg' => $flg,
                    'message' => $message
                );
            }
        }
        echo json_encode($output);
        wp_die();
    }

    function iat_generate_alt_text_callback()
    {
        if (!isset($_POST['image_url']) || !isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'iat_image_alt_text')) {
            wp_send_json_error(['message' => 'Erro de segurança ou URL da imagem não fornecida']);
            wp_die();
        }

        // Pega a URL da imagem enviada via AJAX
        $image_url = sanitize_text_field($_POST['image_url']);

        // Chama a função da OpenAI para gerar o alt text
        $alt_text = generate_alt_text_from_openai($image_url);

        // Retorna o resultado para o JavaScript
        wp_send_json_success(['alt_text' => $alt_text]);

        wp_die();
    }

    public function iat_admin_notices()
    {

        /* get current date */
        $current_date = date('Y-m-d');
        $current_date_string = strtotime($current_date);

        /* get reminde me later date value */
        $remind_me_date = get_option('iat_review_reminder');

        /* get do not show again review */
        $do_not_show = get_option('iat_do_not_show_again');

        if (isset($do_not_show) && $do_not_show != 'yes') {
            if ($remind_me_date < $current_date_string) { ?>
                <div class="notice notice-success is-dismissible review-notice mt-3">
                    <p>
                        <?php
                        _e('If you\'ve found our WordPress plugin <strong>Image Alt Text</strong> helpful, we would greatly appreciate it if you could take a moment to leave us a review. Your feedback helps us improve our plugin and also lets other users know the value of our product. Thank you for taking the time to share your thoughts!', IMAGE_ALT_TEXT);
                        ?>
                    </p>
                    <p>
                        <a role="button" href="https://wordpress.org/support/plugin/image-alt-text/reviews/#new-post" target="_blank" class="button button-primary">
                            <?php _e('Review', IMAGE_ALT_TEXT); ?>
                        </a>
                        <button class="button button-primary is-dismissible" id="remind-me-later">
                            <?php _e('Remind me later', IMAGE_ALT_TEXT); ?>
                        </button>
                        <button class="button button-primary" id="do-not-show-again">
                            <?php _e('Do not show again', IMAGE_ALT_TEXT); ?>
                        </button>
                    </p>
                </div>
<?php }
        }
    }

    function iat_admin_scripts($hook)
    {

        wp_enqueue_script('iat-admin-global', plugins_url('/assets/js/iat-global.js', dirname(__FILE__)), array('jquery'), IAT_FILE_VERSION, true);
        wp_enqueue_script('jquery-ui-tooltip');
    }
}

new iat_general();
