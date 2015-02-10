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
    var staticWarningText = "Watch out! This operation can be a little bit risky."
    var $staticWarning = $("<div>").html(staticWarningText);

    $('body').on('click', undoRollbackSelector, function (e) {
        var $link = $(this);
        var type = $link.hasClass('vp-undo') ? 'undo' : 'rollback';
        var hash = $link.data('commit');

        var data = {
            action: 'vp_prepare_revert_popup',
            type: type,
            hash: hash
        }

        $.post(ajaxurl, data).then(function (data) {
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

    function showRevertPopup ($link, data) {
        var type = $link.hasClass('vp-undo') ? 'undo' : 'rollback';
        var title = type == 'undo' ? "Undo \"" + $link.data('commit-message') + '"' : "Rollback to " + $link.data('commit-date');
        var $content = $('<div>');
        $content.append($staticWarning);
        $content.append('<div class="spinner">');

        $link.webuiPopover({
            title: $('<div class="title-content">').html(title),
            cache: false,
            content: $content.html(),
            closeable: true,
            width: 450,
            style: customRevertPopoverClass,
            placement: 'top-left'
        });

        function maybeHidePopover (e) {
            var popopOverSelector = '.webui-popover-' + customRevertPopoverClass;
            if ($(popopOverSelector).length > 0 && $(popopOverSelector).is(':visible') && jQuery(e.target).parents(popopOverSelector).length == 0 &&
                !$(e.target).is($link) && $(e.target).parents(undoRollbackSelector).length == 0)
            {
                // Hide popover if the click was anywhere but in the link or the popover itself.
                $link.webuiPopover('destroy');
                $('body').off('click', maybeHidePopover);
            }
        };

        $('body').on('click', maybeHidePopover);

        $link.webuiPopover('show');
    }

    function fillPopup($link, data) {
        var $popupContent = $('.webui-popover-' + customRevertPopoverClass + ' .webui-popover-content');
        $popupContent.html(renderPopupContent($link, data));
    }

    function renderPopupContent($link, data) {
        var clearWorkingDirectory = data.clearWorkingDirectory;
        var $content = $('<div>');
        var disableOk = false;
        $content.append($staticWarning);

        if (!clearWorkingDirectory) {
            $content.append("Please commit your changes");
            disableOk = true;
        }

        var $buttonContainer = $('<div>').addClass('button-container');
        var $okButton = $('<a class="button" href="#">OK</a>').attr('href', $link.attr('href'));
        var $cancelButton = $('<a class="button cancel" href="#">Cancel</a>').click(function () { $link.webuiPopover('destroy'); });

        if (disableOk) {
            $okButton.addClass('disabled');
            $okButton.click(function (e) { e.preventDefault(); return false; })
        }

        $buttonContainer.append($okButton).append(' ').append($cancelButton);
        $content.append($buttonContainer);
        return $content;
    }
});