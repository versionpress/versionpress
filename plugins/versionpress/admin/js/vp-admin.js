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

    var customRevertPopoverClass = "versionpress-revert-popover"; // used to identify the popover later
    var undoRollbackSelector = '.vp-undo, .vp-rollback';
    var cancelButtonSelector = '.button.cancel';

    $('body').on('click', undoRollbackSelector, function (e) {
        var $link = $(this);
        var method = $link.hasClass('vp-undo') ? 'undo' : 'rollback';
        var commit = $link.data('commit');

        var data = {
            action: 'vp_show_undo_confirm',
            method: method,
            commit: commit,
            ajax: true
        }

        $.get(ajaxurl, data).then(function (data) {
            if (typeof(data) === "string") {
                data = JSON.parse(data);
            }

            fillPopup($link, data);
        });

        showRevertPopup($link);

        e.preventDefault();
        e.stopPropagation();
        return false;
    });

    function showRevertPopup ($link) {
        var type = $link.hasClass('vp-undo') ? 'undo' : 'rollback';
        var title = type == 'undo' ? "Undo <em>" + $link.data('commit-message') + '</em> ?' : "Rollback to <em>" + $link.data('commit-date') + "</em> ?";
        var $content = $('<div>');
        $content.append('<div class="spinner">');

        $link.webuiPopover({
            title: $('<div class="title-content">').html(title),
            cache: false,
            content: $content.html(),
            closeable: true,
            width: 450,
            style: customRevertPopoverClass,
            placement: 'left-bottom'
        });

        $link.on('hidden.webui.popover', function () {
            $link.webuiPopover('destroy');
        });

        $link.webuiPopover('show');
    }

    function fillPopup($link, data) {
        var $popupContent = $('.webui-popover-' + customRevertPopoverClass + ' .webui-popover-content');
        $popupContent.html(data.body);

        $('.webui-popover-' + customRevertPopoverClass).on('click', cancelButtonSelector, function (e) {
            $link.webuiPopover('destroy');
            return false;
        });
    }
});
