jQuery(document).ready(function($) {

    var $welcomePanelCloseButton = $('#vp-welcome-panel-close-button');
    var $welcomePanel = $('#welcome-panel');
    var $servicePanelButton = $('#vp-service-panel-button');
    var $servicePanel = $('#vp-service-panel');

    $welcomePanelCloseButton.click(function() {

        $.post( ajaxurl, {
            action: 'hide_vp_welcome_panel'
        });

        $welcomePanel.addClass('hidden');
        return false;

    });

    $servicePanelButton.click(toggleServicePanel);

    function toggleServicePanel() {
        if($servicePanel.is(':visible')) {
            $servicePanel.stop().slideUp();
        } else {
            $servicePanel.stop().slideDown();
        }
    }
});