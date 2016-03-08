<?php

namespace VersionPress\Tests\Unit;

use VersionPress\Utils\PathUtils;

class RelativePathsTest extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     * @dataProvider pathProvider
     */
    public function relativePathIsCountedCorrectly($from, $to, $expectedRelativePath) {
        $relativePath = PathUtils::getRelativePath($from, $to);
        $this->assertEquals($expectedRelativePath, $relativePath, "Wrong relative path from: '{$from}' to: '{$to}'.");
    }

    public function pathProvider() {
        $testedRoots = array(
            __DIR__, // existingDirectory
            __DIR__ . '/', // existingDirectory with trailing slash
            '/some/dir', // non-existing directory
            '/some/dir/', // non-existing directory with trailing slash
            str_replace('/', '\\', __DIR__), // existing directory with backslashes
            str_replace('/', '\\', __DIR__) . '/', // existing directory with backslashes and trailing slash
            'c:\\some\\dir', // Windows-like path
            'c:\\some\\dir\\', // Windows-like path with trailing backslash
            'c:\\some\\dir/', // Windows-like path with trailing slash
        );

        // Used instead of dirname() because of testing Windows paths on Unix-like OS
        function uberDirname($path) {
            $realPath = rtrim($path, '\\/'); // don't care about some trailing slash or backslash

            $backslashPosition = strrpos($realPath, '\\');
            $slashPosition = strrpos($realPath, '/');

            return substr($realPath, 0, max($backslashPosition, $slashPosition));
        }

        $testCasesForRoots = array_map(function ($root) {
            return array(
                array($root, $root, ''),
                array($root, $root . '/subdir', 'subdir'),
                array($root, $root . '/subdir/anothersubdir/some-file.php', 'subdir/anothersubdir/some-file.php'),
                array($root, uberDirname($root), '..'),
                array($root, uberDirname(uberDirname($root)), '../..'),
            );
        }, $testedRoots);

        return call_user_func_array('array_merge', $testCasesForRoots);
    }
}
