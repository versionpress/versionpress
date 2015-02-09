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


    var undoRollbackSelector = '.vp-undo, .vp-rollback';
    $(undoRollbackSelector).click(showRevertPopup);

    function showRevertPopup (e) {
        var $link = $(this);
        var customPopoverClass = "versionpress-revert-popover"; // used to identify the popover later
        var type = $link.hasClass('vp-undo') ? 'undo' : 'rollback';
        var title = type == 'undo' ? "Undo \"" + $link.data('commit-message') + '"' : "Rollback to " + $link.data('commit-date');


        $link.webuiPopover({
            type: "async",
            url: "http://date.jsontest.com/",
            title: title,
            content: function (data) {
                $link.webuiPopover('show');
                return data.time;
            },
            closeable: true,
            width: 450,
            style: customPopoverClass
        });
        $('body').on('click', function (e) {
            var popopOverSelector = '.webui-popover-' + customPopoverClass;
            if ($(popopOverSelector).length > 0 && $(popopOverSelector).is(':visible') && jQuery(e.target).parents(popopOverSelector).length == 0 &&
                !$(e.target).is($link) && $(e.target).parents(undoRollbackSelector).length == 0)
            {
                // Hide popover if the click was anywhere but in the link or the popover itself.
                $link.webuiPopover('hide');
            }
        });

        $link.webuiPopover('show');
        $link.webuiPopover('hide');

        e.preventDefault();
        e.stopPropagation();
        return false;
    }
});