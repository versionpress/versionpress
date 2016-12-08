<?php

namespace VersionPress\Tests\Unit;

use VersionPress\Utils\PathUtils;

class RelativePathsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @dataProvider pathProvider
     */
    public function relativePathIsCountedCorrectly($from, $to, $expectedRelativePath)
    {
        $relativePath = PathUtils::getRelativePath($from, $to);
        $this->assertEquals($expectedRelativePath, $relativePath, "Wrong relative path from: '{$from}' to: '{$to}'.");
    }

    /**
     * @test
     * @dataProvider specialPathsProvider
     */
    public function specialPaths($from, $to, $expected)
    {
        $relativePath = PathUtils::getRelativePath($from, $to);
        $this->assertEquals($expected, $relativePath, "Wrong relative path from: '{$from}' to: '{$to}'.");
    }

    public function specialPathsProvider()
    {
        return [
            ['/some/path/..', '/some/other/path', 'other/path'],
            ['/some/path/.', '/some/other/path', '../other/path'],
            ['/some/path', '/some/other/../path', ''],
            ['/some/path/..', '/some/./other/./path', 'other/path'],
            ['/some/long/../path', '/some/other/path', '../other/path'],
            ['/some/./path', '/some/other/path', '../other/path'],
        ];
    }

    public function pathProvider()
    {
        $testedRoots = [
            __DIR__, // existingDirectory
            __DIR__ . '/', // existingDirectory with trailing slash
            '/some/dir', // non-existing directory
            '/some/dir/', // non-existing directory with trailing slash
            str_replace('/', '\\', __DIR__), // existing directory with backslashes
            str_replace('/', '\\', __DIR__) . '/', // existing directory with backslashes and trailing slash
            'C:\\some\\dir', // Windows-like path
            'C:\\some\\dir\\', // Windows-like path with trailing backslash
            'C:\\some\\dir/', // Windows-like path with trailing slash
            'c:\\some\\dir', // lower-case c:
        ];

        // Used instead of dirname() because of testing Windows paths on Unix-like OS
        function uberDirname($path)
        {
            $realPath = rtrim($path, '\\/'); // don't care about some trailing slash or backslash

            $backslashPosition = strrpos($realPath, '\\');
            $slashPosition = strrpos($realPath, '/');

            return substr($realPath, 0, max($backslashPosition, $slashPosition));
        }

        $testCasesForRoots = array_map(function ($root) {
            return [
                [$root, $root, ''],
                [$root, $root . '/subdir', 'subdir'],
                [$root, $root . '/subdir/anothersubdir/some-file.php', 'subdir/anothersubdir/some-file.php'],
                [$root, uberDirname($root), '..'],
                [$root, uberDirname(uberDirname($root)), '../..'],
            ];
        }, $testedRoots);

        $testCases = call_user_func_array('array_merge', $testCasesForRoots);
        $testCases[] = ['c:\\path', 'C:\\path', '']; // c: vs. C: shouldn't matter (other than that, path calculation is case sensitive even on Windows)

        return $testCases;
    }
}
