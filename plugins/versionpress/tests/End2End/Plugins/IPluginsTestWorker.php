<?php

namespace VersionPress\Tests\End2End\Plugins;

use VersionPress\Tests\End2End\Utils\ITestWorker;

interface IPluginsTestWorker extends ITestWorker {

    /**
     *
     *
     * @param array $pluginInfo Required keys are:
     *                            zipfile: absolute path used for upload
     *                            css-id: id of row representing given plugin on 'wp-admin/plugins.php' page
     *                            name: name of plugin (saved in VP-Plugin-Name tag)
     *                            affected-path: directory or file that should be affected by installation / deleting }
     * @return void
     */
    public function setPluginInfo($pluginInfo);

    public function prepare_installPlugin();
    public function installPlugin();

    public function prepare_activatePlugin();
    public function activatePlugin();

    public function prepare_deactivatePlugin();
    public function deactivatePlugin();

    public function prepare_deletePlugin();
    public function deletePlugin();
}
