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
