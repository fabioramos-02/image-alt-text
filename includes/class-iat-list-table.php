<?php

defined('ABSPATH') or die('Plugin file cannot be accessed directly.');

class class_iat_list_table
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
        /* obter tabela de dados do lado do servidor */
        add_action('wp_ajax_iat_get_missing_alt_media_list', array($this, 'fn_iat_get_missing_alt_media_list'));
        /* adicionar texto alternativo */
        add_action('wp_ajax_iat_add_alt_txt_action', array($this, 'fn_iat_add_alt_txt_action'));
        /* copiar nome para texto alternativo */
        add_action('wp_ajax_iat_copy_name_to_alt_txt_action', array($this, 'fn_iat_copy_name_to_alt_txt_action'));
        /* todos os nomes de cópias para texto alternativo */
        add_action('wp_ajax_iat_copy_all_name_to_alt_action', array($this, 'fn_iat_copy_all_name_to_alt_action'));
        /* lista alt existente */
        add_action('wp_ajax_iat_get_existing_alt_media_list', array($this, 'fn_iat_get_existing_alt_media_list'));
        /* atualiza texto alternativo existente */
        add_action('wp_ajax_iat_update_alt_txt_action', array($this, 'fn_iat_update_alt_txt_action'));

        // Gerar texto alternativo individual
        add_action('wp_ajax_iat_generate_individual_alt_text', [$this, 'fn_iat_generate_individual_alt_text']);
    }

    public function fn_iat_generate_individual_alt_text()
    {
        // Verificação de permissões e nonce
        check_ajax_referer('iat_nonce_action', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissão negada.']);
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        if ($post_id <= 0) {
            wp_send_json_error(['message' => 'ID da postagem inválido.']);
        }

        $image_url = wp_get_attachment_url($post_id);
        if (!$image_url) {
            wp_send_json_error(['message' => 'URL da imagem não encontrada.']);
        }

        $openai_key = getenv('OPENAI_API_KEY');

        if (!$openai_key) {
            wp_send_json_error(['message' => 'Chave da API do OpenAI não configurada.']);
        }

        $api_endpoint = "https://api.openai.com/v1/chat/completions";

        $request_data = [
            'model'    => 'gpt-4o',
            'messages' => [
                [
                    'role' => "user",
                    'content' => [
                        ['type' => "text", 'text' => "Descreva está imagem com linguagem simles e acessível para ser utilzado em um site governamental"],
                        [
                            'type' => "image_url",
                            'image_url' => [
                                "url" => $image_url,
                            ],
                        ]
                    ],
                ],
            ],
        ];

        $response = wp_remote_post($api_endpoint, [
            'headers' => [
                'Authorization' => "Bearer $openai_key",
                'Content-Type'  => 'application/json',
            ],
            'body'    => json_encode($request_data),
        ]);

        if (is_wp_error($response)) {
            error_log('API OpenAI Error: ' . $response->get_error_message());
            wp_send_json_error(['message' => 'Erro na requisição à API do OpenAI.']);
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['choices'][0]['message']['content'])) {
            $alt_text = sanitize_text_field($body['choices'][0]['message']['content']);
            update_post_meta($post_id, '_wp_attachment_image_alt', $alt_text);
            wp_send_json_success(['message' => 'Texto alternativo gerado com sucesso.', 'alt_text' => $alt_text]);
        } else {
            error_log('API OpenAI Response: ' . wp_remote_retrieve_body($response));
            wp_send_json_error(['message' => 'Resposta inválida da API do OpenAI.']);
        }
    }
    public function fn_iat_get_missing_alt_media_list()
    {

        /* get all media post_type is attachment */
        $posts_args = array(
            'post_type' => 'attachment',
            'numberposts' => -1
        );
        $posts = get_posts($posts_args);

        $missing_alt_media_list_array = array();
        $post_alt = '';

        /* get site url */
        $site_url = site_url();
        $url = '';

        if (isset($posts) && !empty($posts)) {
            foreach ($posts as $post) {
                if ($post->ID) {
                    $post_id = $post->ID;
                    $post_mime_type = sanitize_mime_type($post->post_mime_type);
                    $post_title = $post->post_title;
                    $url = wp_get_original_image_url($post_id);

                    $post_date = date("F j, Y, g:i a", strtotime($post->post_date));
                    if (str_contains($post_mime_type, 'image')) {
                        $post_alt = get_post_meta($post_id, '_wp_attachment_image_alt', true);
                        if ($post_alt == '') {
                            $missing_alt_media_list_array[] = array(
                                'post_id' => $post_id,
                                'post_image' => $url,
                                'post_title' => $post_title,
                                'post_url' => $url,
                                'post_date' => $post_date
                            );
                        }
                    }
                }
            }
        }
        echo json_encode(array('data' => $missing_alt_media_list_array));
        wp_die();
    }

    public function fn_iat_get_existing_alt_media_list()
    {

        /* get all media post_type is attachment */
        $posts_args = array(
            'post_type' => 'attachment',
            'numberposts' => -1
        );
        $posts = get_posts($posts_args);
        $existing_alt_media_list_array = array();
        $post_alt = '';

        /* get site url */
        $site_url = site_url();
        $url = '';

        if (isset($posts) && !empty($posts)) {
            foreach ($posts as $post) {
                if ($post->ID) {
                    $post_id = $post->ID;
                    $post_mime_type = sanitize_mime_type($post->post_mime_type);
                    $post_title = $post->post_title;
                    $url = wp_get_original_image_url($post_id);

                    $post_date = date("F j, Y, g:i a", strtotime($post->post_date));
                    if (str_contains($post_mime_type, 'image')) {
                        $post_alt = get_post_meta($post_id, '_wp_attachment_image_alt', true);
                        $post_alt = sanitize_text_field($post_alt);
                        if ($post_alt != '') {
                            $existing_alt_media_list_array[] = array(
                                'post_id' => $post_id,
                                'post_image' => $url,
                                'post_title' => $post_title,
                                'post_url' => $url,
                                'post_date' => $post_date,
                                'alt_text' => $post_alt
                            );
                        }
                    }
                }
            }
        }
        echo json_encode(array('data' => $existing_alt_media_list_array));
        wp_die();
    }

    public function fn_iat_add_alt_txt_action()
    {

        $post_id = '';
        if (isset($_POST['post_id']) &&  $_POST['post_id'] != '') {
            $post_id = sanitize_text_field($_POST['post_id']);
        }

        $alt_text = '';
        if (isset($_POST['alt_text']) &&  $_POST['alt_text'] != '') {
            $alt_text = sanitize_text_field($_POST['alt_text']);
        }

        if ($alt_text != '') {
            $alt_txt_updated = update_post_meta($post_id, '_wp_attachment_image_alt', $alt_text);
            if ($alt_txt_updated) {
                $flg = 1;
                $message = esc_html(__('Texto alternativo adicionado.', IMAGE_ALT_TEXT));
                $output = array(
                    'flg' => $flg,
                    'message' => $message,
                );
            }
        } else {
            $flg = 0;
            $message = esc_html(__('Insira o texto alternativo para atualizar.', IMAGE_ALT_TEXT));
            $output = array(
                'flg' => $flg,
                'message' => $message,
            );
        }
        /* Count Images without Alt Text */
        $image_count = $this->iat_missing_alt_media();
        $output['total'] = $image_count;
        echo json_encode($output);
        wp_die();
    }

    public function fn_iat_copy_name_to_alt_txt_action()
    {
        $post_id = '';
        $image_count =  0;
        if (isset($_POST['post_id']) && $_POST['post_id'] != '') {
            $post_id = sanitize_text_field($_POST['post_id']);
        }

        $name_to_alt = '';
        if (isset($_POST['name_to_alt']) && $_POST['name_to_alt'] != '') {
            $name_to_alt = sanitize_text_field($_POST['name_to_alt']);
        }

        if ($name_to_alt != '') {
            $alt_txt_updated = update_post_meta($post_id, '_wp_attachment_image_alt', $name_to_alt);
            if ($alt_txt_updated) {
                $flg = 1;
                $message = esc_html(__('Nome copiado adicionado como texto alternativo.', IMAGE_ALT_TEXT));
                $output = array(
                    'flg' => $flg,
                    'message' => $message,
                );
            }
        } else {
            $flg = 0;
            $message = esc_html(__('Something is wrong to copied text.', IMAGE_ALT_TEXT));
            $output = array(
                'flg' => $flg,
                'message' => $message,
            );
        }
        /* Count Images without Alt Text */
        $image_count = $this->iat_missing_alt_media();
        $output['total'] = $image_count;
        echo json_encode($output);
        wp_die();
    }

    public function fn_iat_copy_all_name_to_alt_action()
    {

        /* check nonce */
        $wp_nonce = sanitize_text_field($_POST['nonce']);
        if (!wp_verify_nonce($wp_nonce, 'iat_copy_all_name_to_alt_nonce')) {
            $message = esc_html(__('Nome copiado adicionado como texto alternativo.', IMAGE_ALT_TEXT));
            die((__('Verificação de segurança. Hacking não permitido', IMAGE_ALT_TEXT)));
        }

        /* ajax call */
        $ajax_call = sanitize_text_field($_POST['ajax_call']);

        /* count how many ajax call will apply */
        $posts_check_sql = 'select ID from ' . $this->wp_posts . ' where post_type = "attachment" AND post_mime_type LIKE "%image%"';
        $posts_result = $this->conn->get_results($posts_check_sql);
        $posts_count = count($posts_result);
        if ($posts_count == '' || $posts_count == 0) {
            $flg = 0;
            $message = esc_html(__('No data available.', IMAGE_ALT_TEXT));
            $output = array(
                'flg' => $flg,
                'message' => $message,
            );
        }

        $per_post = 100;
        $offset = ($ajax_call - 1) * $per_post;

        /* count total ajx call */
        $total_ajax_call = ceil(($posts_count / $per_post));

        /* get all media post_type is attachment */
        $posts_sql = 'select * from ' . $this->wp_posts . ' where post_type = "attachment" AND post_mime_type LIKE "%image%" LIMIT ' . $per_post . ' OFFSET ' . $offset . '';
        $posts = $this->conn->get_results($posts_sql);

        $post_alt = '';
        if (isset($posts) && !empty($posts)) {
            foreach ($posts as $post) {
                if ($post->ID) {
                    $post_id = $post->ID;
                    $post_mime_type = sanitize_mime_type($post->post_mime_type);
                    $post_title = sanitize_title($post->post_title);
                    if (str_contains($post_mime_type, 'image')) {
                        if ($post_title != '') {
                            $post_alt = get_post_meta($post_id, '_wp_attachment_image_alt', true);
                            if ($post_alt == '') {
                                $alt_txt_updated = update_post_meta($post_id, '_wp_attachment_image_alt', $post_title);
                                if ($alt_txt_updated) {
                                    $flg = 1;
                                    $message = esc_html(__('Nome copiado adicionado como texto alternativo.', IMAGE_ALT_TEXT));
                                    $output = array(
                                        'flg' => $flg,
                                        'message' => $message,
                                        'ajax_call' => $ajax_call,
                                        'total_ajax_call' => $total_ajax_call
                                    );
                                } else {
                                    $flg = 0;
                                    $message = esc_html(__('Algo está errado no texto copiado.', IMAGE_ALT_TEXT));
                                    $output = array(
                                        'flg' => $flg,
                                        'message' => $message,
                                    );
                                }
                            } else {
                                $flg = 1;
                                $message = esc_html(__('Nome copiado adicionado como texto alternativo.', IMAGE_ALT_TEXT));
                                $output = array(
                                    'flg' => $flg,
                                    'message' => $message,
                                    'ajax_call' => $ajax_call,
                                    'total_ajax_call' => $total_ajax_call
                                );
                            }
                        } else {
                            $flg = 0;
                            $message = esc_html(__('Something is wrong to copied text.', IMAGE_ALT_TEXT));
                            $output = array(
                                'flg' => $flg,
                                'message' => $message,
                            );
                        }
                    }
                }
            }
        } else {
            $flg = 0;
            $message = esc_html(__('Não há dados disponíveis.', IMAGE_ALT_TEXT));
            $output = array(
                'flg' => $flg,
                'message' => $message,
            );
        }
        $image_count = $this->iat_missing_alt_media();
        $output['total'] = $image_count;
        echo json_encode($output);
        wp_die();
    }

    public function fn_iat_update_alt_txt_action()
    {

        $post_id = '';
        if (isset($_POST['post_id']) &&  $_POST['post_id'] != '') {
            $post_id = sanitize_text_field($_POST['post_id']);
        }

        $ex_alt_text = '';
        if (isset($_POST['ex_alt_text']) &&  $_POST['ex_alt_text'] != '') {
            $ex_alt_text = sanitize_text_field($_POST['ex_alt_text']);
        }

        if ($ex_alt_text != '') {
            $ex_alt_txt_updated = update_post_meta($post_id, '_wp_attachment_image_alt', $ex_alt_text);
            if ($ex_alt_txt_updated) {
                $flg = 1;
                $message = esc_html(__('Texto alternativo atualizado.', IMAGE_ALT_TEXT));
                $output = array(
                    'flg' => $flg,
                    'message' => $message,
                );
            } else {
                $flg = 0;
                $message = esc_html(__('Texto alternativo inserido igual ao anterior. Por favor, adicione novo texto alternativo.', IMAGE_ALT_TEXT));
                $output = array(
                    'flg' => $flg,
                    'message' => $message,
                );
            }
        } else {
            $flg = 0;
            $message = esc_html(__('Insira o texto alternativo para atualizar.', IMAGE_ALT_TEXT));
            $output = array(
                'flg' => $flg,
                'message' => $message,
            );
        }
        echo json_encode($output);
        wp_die();
    }

    public function iat_missing_alt_media()
    {
        $sql = "SELECT count(*) as total FROM " . $this->wp_posts . " as wp, " . $this->wp_postmeta . " as pm where wp.post_mime_type like '%image%' and wp.ID = pm.post_id and pm.meta_key= '_wp_attachment_image_alt' and pm.meta_value = ''";
        $result = $this->conn->get_results($sql);
        if (isset($result) && isset($result[0])) {
            return $count = $result[0]->total;
        } else {
            return $count = 0;
        }
    }
}

$class_iat_list_table = new class_iat_list_table();
