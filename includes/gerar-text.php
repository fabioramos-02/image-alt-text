<?php>
// Função para gerar texto alternativo para todas as imagens sem alt text
function gerar_alt_texto_em_massa() {
    // Verifica se o usuário tem permissão para executar essa ação
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permissão negada.'));
    }

    // Obter todas as imagens que não possuem texto alternativo (alt text)
    $args = array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'meta_query' => array(
            array(
                'key' => '_wp_attachment_image_alt',
                'value' => '',
                'compare' => 'NOT EXISTS' // Apenas imagens sem alt text
            )
        ),
        'posts_per_page' => -1, // Pegar todas as imagens
    );
    
    $imagens_sem_alt = get_posts($args);

    if ($imagens_sem_alt) {
        foreach ($imagens_sem_alt as $imagem) {
            // Gerar o texto alternativo para cada imagem usando a API do ChatGPT
            $url_imagem = wp_get_attachment_url($imagem->ID);
            $alt_text = gerar_alt_texto_com_chatgpt($url_imagem); // Função para gerar alt text
            
            // Atualizar o alt text da imagem
            update_post_meta($imagem->ID, '_wp_attachment_image_alt', $alt_text);
        }
        
        wp_send_json_success(array('message' => 'Texto alternativo gerado para todas as imagens!'));
    } else {
        wp_send_json_error(array('message' => 'Nenhuma imagem encontrada sem alt text.'));
    }

    wp_die(); // Finaliza a execução da requisição Ajax
}

// Registrando a ação AJAX
add_action('wp_ajax_generate_bulk_alt_text', 'gerar_alt_texto_em_massa');
