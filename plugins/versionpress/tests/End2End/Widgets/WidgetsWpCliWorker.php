<?php

namespace VersionPress\Tests\End2End\Widgets;

use VersionPress\Tests\End2End\Utils\WpCliWorker;

class WidgetsTestWpCliWorker extends WpCliWorker implements IWidgetsTestWorker {

    private $sidebar = 'sidebar-1';

    public function prepare_createWidget() {
    }

    public function createWidget() {
        $this->wpAutomation->runWpCliCommand('widget', 'add', array('calendar', $this->sidebar));
    }

    public function prepare_editWidget() {
    }

    public function editWidget() {
        $this->wpAutomation->runWpCliCommand('widget', 'update', array('calendar-1', 'title' => 'Calendar'));
    }

    public function prepare_deleteWidget() {
    }

    public function deleteWidget() {
        $this->wpAutomation->runWpCliCommand('widget', 'delete', array('calendar-1'));
    }
}