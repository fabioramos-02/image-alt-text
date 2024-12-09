<?php
function generate_alt_text_from_openai($image_url)
{
    // Verifica se a chave da API está definida
    $api_key = getenv('OPENAI_API_KEY');
    if (!$api_key) {
        return 'Erro: chave da API da OpenAI não configurada.';
    }

    // Define o endpoint da API da OpenAI
    $url = 'https://api.openai.com/v1/completions';

    // Dados para a requisição
    $data = array(
        'model' => 'gpt-3.5-turbo', // Ou o modelo que você escolher
        'messages' => [
            ['role' => 'system', 'content' => 'Gerar texto alternativo para imagem'],
            ['role' => 'user', 'content' => 'Descreva a imagem para ser inserida em um site governamental, seja claro, simples e utilize linguagem cidadã: ' . $image_url]
        ]
    );

    // Envia a requisição para a API
    $response = wp_remote_post($url, array(
        'method'    => 'POST',
        'body'      => json_encode($data),
        'headers'   => array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $api_key
        ),
    ));

    // Verifica se houve erro na requisição
    if (is_wp_error($response)) {
        return 'Erro ao gerar texto alternativo: ' . $response->get_error_message();
    }

    // Obtém o corpo da resposta
    $result = wp_remote_retrieve_body($response);

    // Converte o resultado JSON para um array PHP
    $response_data = json_decode($result, true);

    // Verifica se a resposta é válida
    if (isset($response_data['choices'][0]['message']['content'])) {
        return $response_data['choices'][0]['message']['content'];
    } else {
        return 'Descrição não encontrada.';
    }
}
