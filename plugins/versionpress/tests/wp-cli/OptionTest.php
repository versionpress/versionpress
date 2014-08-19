<?php

class OptionTest extends WpCliTestCase {
    private $someOption = array(
        "option_name" => "vp_test_option",
        "option_value" => "some value"
    );

    public function testNewOption() {
        WpAutomation::createOption($this->someOption["option_name"], $this->someOption["option_value"]);

        $lastCommit = $this->getLastCommit();
        $comitAction = $lastCommit->getMessage()->getVersionPressTag(ChangeInfo::ACTION_TAG);
        $this->assertStringStartsWith("option/create", $comitAction);

        list($_, $__, $optionId) = explode("/", $comitAction, 3);
        $optionPost = $this->getCommitedEntity($optionId);
        $this->assertEntityEquals($this->someOption, $optionPost);
    }

    protected function getCommitedEntity($vpId) {
        $path = self::$config->getSitePath() . '/wp-content/plugins/versionpress/db/options.ini';
        $options = IniSerializer::deserialize(file_get_contents($path), true);
        return $options[$vpId];
    }

    private function assertOptionEditation($option, $changes) {
        $this->assertEditation($option, $changes, "option/edit", "WpAutomation::createOption", "WpAutomation::editOption");
    }

    private function assertOptionDeletion($option) {
        $this->assertDeletion($option, "option", "WpAutomation::createOption", "WpAutomation::deleteOption");
    }
} 