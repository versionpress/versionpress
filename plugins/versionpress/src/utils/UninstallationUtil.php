<?php

/**
 * Small helper class
 */
class UninstallationUtil {

    /**
     * Returns true if VP uninstallation should remove the Git repo from the root folder (and back it
     * up somewhere). This is true if the first commit is VersionPress commit. Otherwise, the Git repo
     * was probably created by the user before VP was installed and we should keep the repo untouched.
     *
     * @return bool
     */
    public static function uninstallation_should_remove_git_repo() {
        $initialCommit = Git::getInitialCommit();
        return VersionPressChangeInfo::matchesCommitMessage($initialCommit->getMessage());
    }
}