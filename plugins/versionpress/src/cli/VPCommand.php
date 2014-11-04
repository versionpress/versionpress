<?php

class VPCommand extends WP_CLI_Command
{
    const BRANCH_FLAG = "branch";

    /**
     * Clone the WordPress site
     *
     * ## OPTIONS
     *
     * --branch=<name>
     * : Name for created branch.
     * 
     *
     * @subcommand clone
     */
    public function clone_($args = array(), $flags = array()) {
        $branch = $flags[self::BRANCH_FLAG];
        $this->doClone($branch);
    }

    private function doClone($branch) {
        $currentWpPath = get_home_path();
        $branchPath = sprintf("%s/%s_%s", dirname($currentWpPath), basename($currentWpPath), $branch);
        Git::cloneRepository($currentWpPath, $branchPath);
    }
}