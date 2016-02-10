<?php

namespace VersionPress\Utils;

/**
 * Inspired by http://stackoverflow.com/a/6967839/1243495
 */
class Mutex {

    /** @var string Name of the mutex */
    private $name;
    /** @var int Timeout for release (in seconds) */
    private $timeout;
    /** @var bool */
    private $isLockedByThisThread;

    public function __construct($name, $timeout = 60) {
        $this->name = $name;
        $this->timeout = $timeout;
    }

    public function __destruct() {
        $this->release();
    }

    /**
     * Locks the mutex. The lock is automatically released after $timeout.
     */
    public function lock() {
        clearstatcache();
        $lockname = $this->getLockName();
        $dirCreated = @filectime($lockname);

        while (!@mkdir($lockname)) {
            if ($dirCreated) {
                if ((time() - $dirCreated) > $this->timeout) {
                    rmdir($lockname);
                    $dirCreated = false;
                }
            }
            usleep(rand(50000, 200000)); // wait random time
        }

        $this->isLockedByThisThread = true;
    }

    /**
     * Releases the lock. Returns false on failure.
     * @return bool
     */
    public function release() {
        if ($this->isLockedByThisThread) {
            $this->isLockedByThisThread = false;
            return rmdir($this->getLockName());
        }

        return false;
    }

    private function getLockName() {
        return $this->name . '.lock';
    }
}
