<?php

use Composer\Autoload\ClassLoader;
use VersionPress\Storages\Mirror;
use VersionPress\Storages\StorageFactory;
use VersionPress\Tests\Utils\HookMock;

$opts = ['from:', 'to:'];
$args = getopt('', $opts);

if (count($args) < count($opts)) {
    die("Please specify all arguments\n");
}

require_once(__DIR__ . '/../../vendor/autoload.php');

\Tracy\Debugger::enable(\Tracy\Debugger::DEVELOPMENT, sys_get_temp_dir());

$classloader = new ClassLoader();
$classloader->addPsr4('VersionPress\\', __DIR__ . '/../../src');
$classloader->register();

HookMock::setUp(HookMock::TRUE_HOOKS);

// @codingStandardsIgnoreLine
class FooChangeInfo extends \VersionPress\ChangeInfos\TrackedChangeInfo
{

    private $files;

    public function __construct($files)
    {
        $this->files = $files;
    }

    public function getChangeDescription()
    {
        return join("\n", $this->files);
    }

    public function getScope()
    {
        return "file";
    }

    public function getAction()
    {
        return "create";
    }

    protected function getActionTagValue()
    {
        return "{$this->getScope()}/{$this->getAction()}";
    }

    public function getCustomTags()
    {
        return [];
    }

    public function getChangedFiles()
    {
        return array_map(function ($file) {
            return [
                'type' => 'path',
                'path' => $file
            ];
        }, $this->files);
    }

    public function getPriority()
    {
        return 10;
    }


}


define('ABSPATH', sys_get_temp_dir()); // fake
define('VERSIONPRESS_PLUGIN_DIR', sys_get_temp_dir()); // fake
define('VERSIONPRESS_TEMP_DIR', sys_get_temp_dir()); // fake
$repositoryDir = sys_get_temp_dir() . '/vp-repository';

$changeList = createFiles($repositoryDir, $args['from'], $args['to']);

$mirror = Mockery::mock(Mirror::class);
$mirror->shouldReceive('getChangeList')->andReturn($changeList);

$storageFactory = Mockery::mock(StorageFactory::class);

$gitRepository = new \VersionPress\Git\GitRepository($repositoryDir, sys_get_temp_dir());

/**
 * @var Mirror $mirror
 * @var StorageFactory $storageFactory
 */

global $versionPressContainer;
$versionPressContainer = new \VersionPress\DI\DIContainer();
$versionPressContainer->register(\VersionPress\DI\VersionPressServices::CHANGEINFO_FACTORY, function () {
    return Mockery::mock(\VersionPress\ChangeInfos\ChangeInfoFactory::class);
});

$committer = new \VersionPress\Git\Committer($mirror, $gitRepository, $storageFactory);
$committer->commit();
function createFiles($dir, $from, $to)
{
    return array_map(function ($n) use ($dir) {
        $file = $dir . "/$n.txt";
        touch($file);
        return createChangeInfo([$file]);
    }, range($from, $to - 1));
}

function createChangeInfo($files)
{
    $fooChangeInfo = new FooChangeInfo($files);
    return $fooChangeInfo;
}


function is_user_logged_in()
{
    return false;
}

function get_plugin_data()
{
    return ['Version' => '0.0'];
}
