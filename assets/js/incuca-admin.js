jQuery(document).ready(function($) {
    $('#api-key-status').addClass('alert-info').text('Verificando chave de API...').show();
    var api_key = $('#incuca_api_key').val();
    $.post(incuca_ajax.ajax_url, {
        action: 'incuca_check_api_key',
        api_key: api_key,
    }, function(response) {
        $('#api-key-status').removeClass('alert-info');
        if (response.success) {
            $('#api-key-status').addClass('alert-success').text('Chave de API válida.');
            $('#show_script_div').css('opacity', '1');
        } else {
            $('#api-key-status').addClass('alert-danger').text('Chave de API inválida.');
        }
    });
    $('#incuca_insert_script').on('change', function() {
        var showScript = $(this).is(':checked') ? 1 : 0;
        $.post(ajaxurl, {
            action: 'incuca_check_show_script',
            show_script: showScript
        }, function(response) {
            $('#show-script-status').css('opacity', '1');
            if (response.success) {
                $('#show-script-status').addClass('alert-success').text('Configuração alterada.');
            } else {
                $('#show-script-status').addClass('alert-danger').text('Erro ao atualizar configuração.');
            }
        });
    });
});