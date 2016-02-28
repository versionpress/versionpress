<?php

$opts = array('from:', 'to:');
$args = getopt('', $opts);

if (count($args) < count($opts)) {
    die("Please specify all arguments\n");
}

require_once(__DIR__ . '/../../vendor/autoload.php');

\Tracy\Debugger::enable(\Tracy\Debugger::DEVELOPMENT, __DIR__);

$classloader = new \Composer\Autoload\ClassLoader();
$classloader->addPsr4('VersionPress\\', __DIR__ . '/../../src');
$classloader->register();

class FooChangeInfo extends \VersionPress\ChangeInfos\TrackedChangeInfo {

    private $files;

    public function __construct($files) {
        $this->files = $files;
    }

    function getChangeDescription() {
        return join("\n", $this->files);
    }

    static function buildFromCommitMessage(\VersionPress\Git\CommitMessage $commitMessage) {
    }

    function getEntityName() {
        return "file";
    }

    function getAction() {
        return "create";
    }

    protected function getActionTagValue() {
        return "{$this->getEntityName()}/{$this->getAction()}";
    }

    public function getCustomTags() {
        return array();
    }

    public function getChangedFiles() {
        return array_map(function ($file) {
            return array(
                'type' => 'path',
                'path' => $file
            );
        }, $this->files);
    }
}


define('ABSPATH', __DIR__); // fake
define('VERSIONPRESS_PLUGIN_DIR', __DIR__); // fake
define('VP_VPDB_DIR', __DIR__); // fake
$repositoryDir = __DIR__ . '/repository';

$changeList = createFiles($repositoryDir, $args['from'], $args['to']);

$mirror = getMock('\VersionPress\Storages\Mirror');
$mirror->expects(new PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)
    ->method('getChangeList')
    ->will(new PHPUnit_Framework_MockObject_Stub_Return($changeList));

$storageFactory = getMock('\VersionPress\Storages\StorageFactory');

$gitRepository = new \VersionPress\Git\GitRepository($repositoryDir, __DIR__);

/**
 * @var \VersionPress\Storages\Mirror $mirror
 * @var \VersionPress\Storages\StorageFactory $storageFactory
 */

$committer = new \VersionPress\Git\Committer($mirror, $gitRepository, $storageFactory);
$committer->commit();
function createFiles($dir, $from, $to) {
    return array_map(function ($n) use ($dir) {
        $file = $dir . "/$n.txt";
        touch($file);
        return createChangeInfo(array($file));
    }, range($from, $to - 1));
}

/**
 * @param $originalClassName
 * @return PHPUnit_Framework_MockObject_MockObject
 */
function getMock($originalClassName) {
    return PHPUnit_Framework_MockObject_Generator::getMock(
        $originalClassName,
        array(),
        array(),
        '',
        false);
}

/**
 * @param $className
 * @return PHPUnit_Framework_MockObject_MockObject
 */
function getMockForAbstractClass($className) {
    return PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass($className);
}

function createChangeInfo($files) {

    $fooChangeInfo = new FooChangeInfo($files);


    return $fooChangeInfo;
}


function is_user_logged_in() {
    return false;
}

function get_plugin_data() {
    return array('Version' => '0.0');
}
