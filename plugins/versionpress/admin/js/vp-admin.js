jQuery(document).ready(function($) {

    if ($('#welcome-panel').length) {
        $('#vp-page-header').hide();
    }

    $('#vp-welcome-panel-close-button').click(function() {

        $.post( ajaxurl, {
            action: 'hide_vp_welcome_panel'
        });

        $('#welcome-panel').addClass('hidden');
        $('#vp-page-header').show();

        return false;

    });

});