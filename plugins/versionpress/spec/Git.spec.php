<?php

\PhpJasmine\Expectation::addMatcher('toBeGitRepository', 'GitRepositoryMatcher', null);

describe("git", function () {
    $emptyDirectoryPath = __DIR__ . '/workspace/empty-directory';
    $initializedRepositoryPath = __DIR__ . '/workspace/initialized-repository';

    xdescribe("create repository", function () use ($emptyDirectoryPath) {
        afterEach(function () use($emptyDirectoryPath) {
            purgeDirectory($emptyDirectoryPath);
        });

        it("should create git repository on given path", function () use ($emptyDirectoryPath) {
            Git::createGitRepository($emptyDirectoryPath);
            expect($emptyDirectoryPath)->toBeGitRepository();
        });
    });

    xdescribe("detection", function () use ($emptyDirectoryPath) {

        afterEach(function () use($emptyDirectoryPath) {
            purgeDirectory($emptyDirectoryPath);
        });

        it("should determine non-versioned directory", function () use ($emptyDirectoryPath) {
            $isVersioned = Git::isVersioned($emptyDirectoryPath);
            expect($isVersioned)->toBe(false);
        });

        it("should detect versioned directory", function () use ($emptyDirectoryPath) {
            Git::createGitRepository($emptyDirectoryPath);
            $isVersioned = Git::isVersioned($emptyDirectoryPath);
            expect($isVersioned)->toBe(true);
        });
    });

    xdescribe("log", function () use ($initializedRepositoryPath) {


        beforeEach(function () use ($initializedRepositoryPath) {
            chdir($initializedRepositoryPath);
        });

        afterEach(function () use($initializedRepositoryPath) {
            chdir($initializedRepositoryPath);
            exec("git update-ref -d HEAD"); // delete all commits
        });

        it("should be empty in initialized repository", function () use ($initializedRepositoryPath) {
            $log = Git::log();
            expect($log)->toEqual([]);
        });

        it("should reflect commits", function () use ($initializedRepositoryPath) {
            touch($initializedRepositoryPath . '/foo.txt');
            Git::commit('First commit', $initializedRepositoryPath);
            $log = Git::log();
            expect(count($log))->toBe(1);
        });

        it("should prepend VP prefix", function () use ($initializedRepositoryPath) {
            touch($initializedRepositoryPath . '/foo.txt');
            $commitMessage = 'First commit';
            $prefix = '[VP] ';
            Git::commit($commitMessage, $initializedRepositoryPath);
            $log = Git::log();
            expect($log[0]['message'])->toBe($prefix . $commitMessage);
        });
    });

    describe("revert", function () use ($initializedRepositoryPath) {

        beforeEach(function () use ($initializedRepositoryPath) {
            chdir($initializedRepositoryPath);
        });

        afterEach(function () use($initializedRepositoryPath) {
            chdir($initializedRepositoryPath);
            exec("git update-ref -d HEAD"); // delete all commits
            exec("git rm -rf *"); // delete all files
        });

        function prepareCommits($file, $contents) {
            $i = 1;
            foreach($contents as $content) {
                file_put_contents($file, $content);
                Git::commit("$i. commit", dirname($file));
            }
        }

        it("should undo changes in files", function () use ($initializedRepositoryPath) {
            $changedFile = $initializedRepositoryPath . '/foo.txt';
            prepareCommits($changedFile, ['foo', 'bar']);

            chdir($initializedRepositoryPath);
            $log = Git::log();
            $firstCommitId = $log[1]['id'];
            Git::revert($firstCommitId);
            expect(file_get_contents($changedFile))->toBe('foo');
        });

        it("should create new commit" , function () use ($initializedRepositoryPath) {
            $changedFile = $initializedRepositoryPath . '/foo.txt';
            prepareCommits($changedFile, ['foo', 'bar']);

            chdir($initializedRepositoryPath);
            $log = Git::log();
            $firstCommitId = $log[1]['id'];
            Git::revert($firstCommitId);
            $log = Git::log();
            expect(count($log))->toBe(3);
        });
    });
});



function purgeDirectory($path) {
    $iterator = new RecursiveDirectoryIterator($path);
    $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);
    try {
        foreach($files as $file) {
            /** @var $file SplFileInfo */
            if($file->getFilename() === '.' || $file->getFilename() === '..')
                continue;
            if($file->isDir())
                rmdir($file->getRealPath());
            else
                unlink($file->getRealPath());
        }
    } catch (Exception $e) {
        print_r($e->getMessage());
    }
}

class GitRepositoryMatcher implements \PhpJasmine\Matcher {

    private $path;

    public function __construct($dummy) {
    }

    public function matches($path) {
        $this->path = $path;
        return is_dir($this->path . '/.git');
    }

    public function getFailureMessage() {
        return "expected git repository in directory " . var_export($this->path, true);
    }

    public function getNegativeFailureMessage() {
        return "git repository not expected in directory " . var_export($this->path, true);
    }
}

class NDebugger {
    public static function log($message) {
//        print_r($message . "\n");
    }

    public static function __callStatic($name, $arguments) {
        // do nothing
    }
}

function is_user_logged_in() {
    return false;
}