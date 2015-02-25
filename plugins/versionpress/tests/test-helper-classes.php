<?php

$directoryIterator = new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS);
$filterIterator = new RecursiveCallbackFilterIterator($directoryIterator, function (SplFileInfo $current, $key, RecursiveDirectoryIterator $iterator) {
    $ignoredDirectories = array('node_modules', 'vagrant');
    $filename = $current->getFilename();

    if (in_array($filename, $ignoredDirectories)) {
        return false;
    }

    if ($iterator->hasChildren()) {
        return true;
    }

    if (!preg_match('/^[A-Z].*\.php/', $filename) || preg_match('/Test\.php$/', $filename)) {
        return false;
    }

    return true;
});
$recursiveFilesIterator = new RecursiveIteratorIterator($filterIterator);
$testHelperClasses = array_map(function (SplFileInfo $fileInfo) { return $fileInfo->getRealPath(); }, iterator_to_array($recursiveFilesIterator));

unset($recursiveFilesIterator, $filterIterator, $directoryIterator);

return $testHelperClasses;