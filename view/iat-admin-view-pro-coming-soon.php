<?php
$header_file = IAT_FILE_PATH . '/view/iat-admin-view-header.php';
if (file_exists($header_file)) {
    include_once $header_file;
}
?>

<div class="container-fluid">
    <div class="wrap">
        <div class="row">
            <p style="font-size:18px;margin:20px 0;color:#003566;"><?php _e('Estamos felizes em anunciar o próximo lançamento do nosso novo SEO and Accessibility Plugin, projetado para otimizar as imagens do seu site e garantir que as melhores práticas sejam atendidas perfeitamente. Este plugin adicionará automaticamente prefixos e pós-fixos personalizáveis ​​aos nomes das imagens, notificará você sobre quaisquer imagens sem texto alternativo e fornecerá uma lista abrangente de imagens que podem precisar de atenção, como aquelas com nomes duplicados ou nomes de arquivo não amigáveis ​​ao SEO. Além disso, ele detectará e sinalizará imagens sem rótulos ARIA apropriados no frontend, ajudando você a atender aos padrões de acessibilidade sem esforço. Fique ligado para uma abordagem mais suave e inteligente ao gerenciamento de imagens e conformidade com SEO!', IMAGE_ALT_TEXT) ?></p>
            <p style="font-size:18px;margin:20px 0;color:#003566;font-weight:800;"><?php _e('Características', IMAGE_ALT_TEXT) ?></p>
            <ul style="list-style-type: none; font-family: Arial, sans-serif; font-size: 16px; color: #333;">
                <li>&#10003; Prefixo e pós-fixo para texto alternativo de imagem.</li>
                <li>&#10003; Detectar imagens sem texto alternativo em páginas da web.</li>
                <li>&#10003; Notificar imagens sem lembrete de texto alternativo.</li>
                <li>&#10003; Listar as imagens que não seguem os padrões de SEO.</li>
                <li>&#10003; Listar as imagens com nomes duplicados.</li>
                <li>&#10003; Listar as imagens com tags alternativas duplicadas.</li>
                <li>&#10003; Listar os nomes de arquivo de imagem duplicados.</li>
                <li>&#10003; Detectar rótulo Aria ausente em páginas da web.</li>
            </ul>

        </div>
        <div class="row col-lg-2">
            <a href="https://imagealttext.in" target="_blank"><button class="btn" style="margin:0 auto;background-color:#003566;color:#fff;"><?php _e('Inscreva-se para ser notificado', IMAGE_ALT_TEXT); ?></button></a>
        </div>
    </div>
</div>