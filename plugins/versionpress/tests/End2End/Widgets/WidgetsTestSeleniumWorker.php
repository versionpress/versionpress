<?php

namespace VersionPress\Tests\End2End\Widgets;

use VersionPress\Tests\End2End\Utils\SeleniumWorker;

class WidgetsTestSeleniumWorker extends SeleniumWorker implements IWidgetsWorker {

    public function prepare_createWidget() {
        $this->url('wp-admin/widgets.php');
        $this->jsClick("#widget-list .widget:contains('Calendar') .widget-control-edit");
        $this->waitAfterRedirect();
    }

    public function createWidget() {
        static $widgetNumber = isset($widgetNumber) ? $widgetNumber + 1 : 1;

        $this->byCssSelector("form[action='widgets.php'] input[name*=title]")->value("Calendar $widgetNumber");
        $this->byCssSelector("form[action='widgets.php'] input[type=submit]")->click();
        $this->waitAfterRedirect();
    }

    public function prepare_editWidget() {
        $this->url('wp-admin/widgets.php');
    }

    public function editWidget() {
        $this->jsClick('#widgets-right .widget-control-edit');
        $this->executeScript("jQuery('#widgets-right .widget .widget-inside input[name*=title]').first().val('Edited title')");
        $this->executeScript("jQuery('#widgets-right .widget .widget-inside input[type=submit]').first().click()");
        $this->waitForAjax();
    }

    public function prepare_deleteWidget() {
        $this->url('wp-admin/widgets.php');
    }

    public function deleteWidget() {
        $this->jsClick('#widgets-right .widget-control-edit');
        $this->executeScript("jQuery('#widgets-right .widget .widget-control-remove').first().click()");
        $this->waitForAjax();
    }
}