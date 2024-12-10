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

        /* localizar script para iat-admin-js */
        wp_localize_script('iat-admin-js', 'iatObj', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('iat_image_alt_text'),
            'msg1' => esc_html(__('Tem certeza de que deseja copiar o texto alt? Se "Com Alt" for selecionado, esta ação atualizará o texto alt existente com o nome do post. Se "Sem Alt" for selecionado, ele preencherá todo o texto alt ausente para itens de mídia usando o nome do post correspondente.', IMAGE_ALT_TEXT)),
            'msg2' => esc_html(__('Tem certeza de que deseja copiar o texto alt? Se "Com Alt" for selecionado, esta ação atualizará o nome do post anexado para texto alt . Se "Sem Alt" for selecionado, ele preencherá todo o texto alt ausente para itens de mídia usando o nome do post anexado correspondente.', IMAGE_ALT_TEXT)),
            'msg3' => esc_html(__('Ótimo, todas as suas imagens têm texto alternativo. Todas as imagens sem texto alternativo aparecerão aqui.', IMAGE_ALT_TEXT)),
            'msg4' => esc_html(__('Tem certeza de que deseja gerar o texto alt?', IMAGE_ALT_TEXT)),
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
                    $message = __('Algo está errado', IMAGE_ALT_TEXT);
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
                $message = __('Algo está errado', IMAGE_ALT_TEXT);
                $output = array(
                    'flg' => $flg,
                    'message' => $message
                );
            }
        }
        echo json_encode($output);
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
                        _e('Se você achou nosso plugin WordPress <strong>Image Alt Text</strong> útil, ficaríamos muito gratos se você pudesse reservar um momento para nos deixar uma avaliação. Seu feedback nos ajuda a melhorar nosso plugin e também permite que outros usuários saibam o valor do nosso produto. Obrigado por reservar um tempo para compartilhar suas ideias!', IMAGE_ALT_TEXT);
                        ?>
                    </p>
                    <p>
                        <a role="button" href="https://wordpress.org/support/plugin/image-alt-text/reviews/#new-post" target="_blank" class="button button-primary">
                            <?php _e('Análise', IMAGE_ALT_TEXT); ?>
                        </a>
                        <button class="button button-primary is-dismissible" id="remind-me-later">
                            <?php _e('Lembre-me mais tarde', IMAGE_ALT_TEXT); ?>
                        </button>
                        <button class="button button-primary" id="do-not-show-again">
                            <?php _e('Não mostrar novamente', IMAGE_ALT_TEXT); ?>
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
