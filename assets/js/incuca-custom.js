jQuery(document).ready(function($) {
    $('#toggle-password').on('click', function() {
        var passwordField = $('#incuca_api_key');
        var passwordFieldType = passwordField.attr('type');
        if (passwordFieldType == 'password') {
            passwordField.attr('type', 'text');
            $(this).html('<i class="fa fa-eye-slash"></i>');
        } else {
            passwordField.attr('type', 'password');
            $(this).html('<i class="fa fa-eye"></i>');
        }
    });
});