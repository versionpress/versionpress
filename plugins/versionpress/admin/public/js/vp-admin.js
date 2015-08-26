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
            commit: commit
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

    var customCompatibilityPopoverClass = "versionpress-compatibility-popover"; // used to identify the popover later

    $('.plugin-install .vp-compatibility').each(function () {
        var $label = $(this);
        var $parentCard = $label.parents('.plugin-card');
        var $originalCompatibilityColumn = $parentCard.find('.column-compatibility');
        var $vpCompatibilityColumn = $originalCompatibilityColumn.clone().empty();

        $label.detach().appendTo($vpCompatibilityColumn);
        $originalCompatibilityColumn.after($vpCompatibilityColumn);
        $label.find('.hide-without-js').show();
    });

    function showCompatibilityPopup($element, $button, buttonText) {
        if ($element.hasClass('vp-compatible')) {
            return;
        }
        var pluginName = $element.data('plugin-name');
        var incompatible = $element.hasClass('vp-incompatible');
        var title = incompatible ? 'This will not end well' : 'Warning';
        var content = '<p>' + pluginName + '<strong>' +
            (incompatible ? ' is not compatible' : ' was not yet tested') + '</strong> with VersionPress.<br>' +
            (incompatible ? 'These plugins will not work correctly when used together.' : 'Some functionality may not work as intended.') + '</p>' +
            '<p><a href="http://docs.versionpress.net/en/integrations/plugins" target="_blank">Learn more</a></p>';
        var buttons = '<div class="vp-compatibility-popup-buttons">' +
            '<a class="button button-primary vp-install-now" href="' + $button.attr('href') + '">' + buttonText + '</a> ' +
            '<a class="button vp-cancel">Cancel</a>' +
            '</div>';

        $button.webuiPopover({
            title: title,
            cache: false,
            content: content + buttons,
            closeable: true,
            width: 450,
            style: customCompatibilityPopoverClass,
            placement: 'auto'
        });

        $button.on('click', function(e) {
            e.stopImmediatePropagation();

            $('.webui-popover-' + customCompatibilityPopoverClass).on('click', '.vp-cancel', function (e) {
                $button.webuiPopover('hide');
                return false;
            });

            return false;
        });
    }

    $('.plugin-install .vp-compatibility').each(function () {
        var $el = $(this);
        var $list = $(this).closest('.plugin-card');
        var $installButton = $list.find('.install-now');

        showCompatibilityPopup($el, $installButton, 'Install');
    });

    $('.plugins .vp-compatibility').each(function () {
        var $el = $(this);
        var $list = $(this).closest('tr');
        var $activateButton = $list.find('.activate a');

        showCompatibilityPopup($el, $activateButton, 'Activate');
    });
});
