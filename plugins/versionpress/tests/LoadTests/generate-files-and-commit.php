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

\Tracy\Debugger::enable(\Tracy\Debugger::DEVELOPMENT, __DIR__);

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
}


define('ABSPATH', __DIR__); // fake
define('VERSIONPRESS_PLUGIN_DIR', __DIR__); // fake
define('VERSIONPRESS_TEMP_DIR', __DIR__); // fake
$repositoryDir = __DIR__ . '/repository';

$changeList = createFiles($repositoryDir, $args['from'], $args['to']);

$mirror = getMock(Mirror::class);
$mirror->expects(new PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)
    ->method('getChangeList')
    ->will(new PHPUnit_Framework_MockObject_Stub_Return($changeList));

$storageFactory = getMock(StorageFactory::class);

$gitRepository = new \VersionPress\Git\GitRepository($repositoryDir, __DIR__);

/**
 * @var Mirror $mirror
 * @var StorageFactory $storageFactory
 */

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

/**
 * @param $originalClassName
 * @return PHPUnit_Framework_MockObject_MockObject
 */
function getMock($originalClassName)
{
    return PHPUnit_Framework_MockObject_Generator::getMock(
        $originalClassName,
        [],
        [],
        '',
        false
    );
}

/**
 * @param $className
 * @return PHPUnit_Framework_MockObject_MockObject
 */
function getMockForAbstractClass($className)
{
    return PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass($className);
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
