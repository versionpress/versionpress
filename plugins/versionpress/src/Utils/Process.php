<?php

namespace VersionPress\Utils;

/**
 * Symfony\Process implementation that adds the `getConsoleOutput()` method and increases the time limit.
 *
 * @package VersionPress\Utils
 */
class Process extends \Symfony\Component\Process\Process {

    private $consoleOutput;

    public function __construct($commandline, $cwd = null, array $env = null, $input = null, $timeout = 3600, array $options = array()) {
        parent::__construct($commandline, $cwd, $env, $input, $timeout, $options);
    }

    /**
     * Returns output as it would appear in the console and doesn't care whether it comes from STDOUT or STDERR (or both).
     *
     * This is useful in client code that just wants to show the user whatever the command printed. The programs
     * will sometimes use STDOUT, sometimes STDERR, sometimes mistakenly STDOUT instead of STDERR etc. but it often
     * doesn't matter, we just want to pass the output on to the user.
     *
     * @return string
     */
    public function getConsoleOutput() {
        return $this->consoleOutput;
    }


    public function start($callback = null) {
        $this->consoleOutput = '';
        parent::start($callback);
    }

    public function addOutput($line) {
        parent::addOutput($line);
        $this->consoleOutput .= $line;
    }

    public function addErrorOutput($line) {
        parent::addErrorOutput($line);
        $this->consoleOutput .= $line;
    }


}
