<?php

class PluginsTest extends SeleniumTestCase {


    /**
     * Required keys are:
     * zipfile: absolute path used for upload
     * css-id: id of row representing given plugin on 'wp-admin/plugins.php' page
     * name: name of plugin (saved in VP-Plugin-Name tag)
     * affected-path: directory or file that should be affected by installation / deleting
     *
     * @var array
     */
    private static $pluginInfo;


    public static function setupBeforeClass() {
        $testDataPath = __DIR__ . DIRECTORY_SEPARATOR . 'test-data' . DIRECTORY_SEPARATOR;
        self::$pluginInfo = array(
            'zipfile' => $testDataPath . 'hello-dolly.1.6.zip',
            'css-id' => 'hello-dolly',
            'name' => 'Hello Dolly',
            'affected-path' => 'hello-dolly/*',
        );
    }

    /**
     * @test
     * @testdox Deleting plugin creates 'plugin/delete' action
     */
    public function deletingPluginCreatesPluginDeleteAction() {
        $this->url("http://127.0.0.1/wordpress/wp-admin/plugins.php");
        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->byCssSelector("#". self::$pluginInfo['css-id'] ." .delete a")->click();
        $this->byCssSelector("#submit")->click();
        $this->waitAfterRedirect();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("plugin/delete");
        $commitAsserter->assertCommitTag("VP-Plugin-Name", self::$pluginInfo['name']);
        $commitAsserter->assertCommitPath("D", "wp-content/plugins/" . self::$pluginInfo['affected-path']);
        $commitAsserter->assertCleanWorkingDirectory();
    }

    /**
     * @test
     * @testdox Uploading plugin creates 'plugin/install' action
     * @depends deletingPluginCreatesPluginDeleteAction
     */
    public function uploadingPluginCreatesPluginInstallAction() {
        $this->loginIfNecessary();
        $this->url('wp-admin/plugin-install.php?tab=upload');

        $commitAsserter = new CommitAsserter($this->gitRepository);

        $this->byCssSelector('#pluginzip')->value(self::$pluginInfo['zipfile']);
        $this->byCssSelector('#install-plugin-submit')->click();
        $this->waitAfterRedirect();

        $commitAsserter->assertNumCommits(1);
        $commitAsserter->assertCommitAction("plugin/install");
        $commitAsserter->assertCommitTag("VP-Plugin-Name", self::$pluginInfo['name']);
        $commitAsserter->assertCommitPath("A", "wp-content/plugins/" . self::$pluginInfo['affected-path']);
        $commitAsserter->assertCleanWorkingDirectory();
    }
}