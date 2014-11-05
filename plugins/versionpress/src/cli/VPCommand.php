<?php

/**
 * VersionPress CLI commands
 */
class VPCommand extends WP_CLI_Command
{
    const NAME_FLAG = "name";

    /**
     * Clone the site to a separate folder, db and Git branch.
     *
     *
     * ## OPTIONS
     *
     * --name=<name>
     * : Name of the clone. Will be used as a suffix of the cloned folder, new
     * database and the name of a new Git branch. For example, if the original
     * site is "wpsite" and the `name` parameter is "test", the clone will be
     * in the folder called "wpsite_test", the database will be "<orig>_test"
     * and the Git branch name will be "test".
     * 
     *
     * @subcommand clone
     */
    public function clone_($args = array(), $flags = array()) {
        $name = $flags[self::NAME_FLAG];
        $this->doClone($name);
    }

    private function doClone($name) {
        $currentWpPath = get_home_path();
        $branchPath = sprintf("%s/%s_%s", dirname($currentWpPath), basename($currentWpPath), $name);
        Git::cloneRepository($currentWpPath, $branchPath);
    }
}