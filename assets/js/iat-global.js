/* review remind me later */
jQuery(document).on('click', '#remind-me-later', function (e) {
    e.preventDefault();
    var data = {
        action: 'iat_remind_me_later'
    }
    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: data,
        success: function (res) {
            var res = JSON.parse(res);
            if (res.flg == '1') {
                jQuery('.review-notice').hide();
            } else {
                alert(res.message);
            }
        }
    });
});

/* review do not show again */
jQuery(document).on('click', '#do-not-show-again', function (e) {
    e.preventDefault();
    var data = {
        action: 'iat_do_not_show_again'
    }
    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: data,
        success: function (res) {
            var res = JSON.parse(res);
            if (res.flg == '1') {
                jQuery('.review-notice').hide();
            } else {
                alert(res.message);
            }
        }
    });

});


//criado com chatgpt

jQuery(document).on('click', '#iat-generate-bulk-alt-text-btn', function() {
    // Mostrar o ícone de carregamento
    jQuery('#iat-generate-bulk-alt-text-loader').show();

    // Recupera o nonce
    var nonce = jQuery('#iat_nonce').val();

    // Enviar a solicitação AJAX para gerar o texto alternativo em massa
    jQuery.ajax({
        url: ajaxurl, // URL padrão para requisições Ajax no WordPress
        method: 'POST',
        data: {
            action: 'generate_bulk_alt_text', // Ação no WordPress
            nonce: nonce // Passando o nonce para o PHP
        },
        success: function(response) {
            // Esconder o ícone de carregamento
            jQuery('#iat-generate-bulk-alt-text-loader').hide();

            if (response.success) {
                alert('Texto alternativo gerado para todas as imagens!');
                location.reload(); // Recarregar a página para exibir as alterações
            } else {
                alert(response.data.message); // Mostrar a mensagem de erro
            }
        },
        error: function() {
            // Esconder o ícone de carregamento em caso de erro
            jQuery('#iat-generate-bulk-alt-text-loader').hide();
            alert('Erro ao gerar texto alternativo em massa.');
        }
    });
});
jQuery(document).ready(function($) {
    $('#iat-generate-bulk-alt-text-btn').click(function() {
        var imageUrls = []; // Array para armazenar as URLs das imagens
        $('.image-url-input').each(function() {
            var imageUrl = $(this).val(); // Supondo que você tenha um campo de entrada para a URL da imagem
            if (imageUrl) {
                imageUrls.push(imageUrl); // Adiciona a URL ao array
            }
        });

        if (imageUrls.length === 0) {
            alert('Por favor, forneça as URLs das imagens.');
            return;
        }

        $('#iat-generate-bulk-alt-text-loader').show();

        $.ajax({
            url: iatObj.ajaxUrl,
            type: 'POST',
            data: {
                action: 'iat_generate_bulk_alt_text', // O nome da ação
                nonce: iatObj.nonce,
                image_urls: imageUrls // Passa o array de URLs de imagem
            },
            success: function(response) {
                $('#iat-generate-bulk-alt-text-loader').hide();

                if (response.success) {
                    alert('Texto alternativo gerado com sucesso para todas as imagens!');
                    console.log(response.data); // Exibe o resultado no console
                } else {
                    alert('Erro: ' + response.data.message);
                }
            },
            error: function() {
                $('#iat-generate-bulk-alt-text-loader').hide();
                alert('Ocorreu um erro ao gerar os textos alternativos.');
            }
        });
    });
});
