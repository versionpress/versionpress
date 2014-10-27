<?php

/**
 * Provides info about a change, or, in other words, represents one row
 * in the main VersionPress table.
 *
 * ChangeInfo is used in two "directions":
 *
 *  1. When the commit is first physically created
 *  2. When the change is later displayed in the main VersionPress table
 *
 * There are two main classes of ChangeInfo objects: tracked and untracked ones, see
 * direct subclasses / implementations.
 *
 * @see TrackedChangeInfo
 * @see UntrackedChangeInfo
 */
interface ChangeInfo {

    /**
     * Creates a commit message from this ChangeInfo. Used by Committer.
     *
     * TODO maybe rename to toCommitMessage() (commit message is not a "property" of ChangeInfo)
     *
     * @see Committer::commit()
     * @return CommitMessage
     */
    function getCommitMessage();

    /**
     * Text displayed in the main VersionPress table. Used by admin/index.php.
     *
     * @return string
     */
    function getChangeDescription();

    /**
     * Factory method - builds a ChangeInfo object from a commit message. The commit
     * message is generally expected to contain VP tags that will fit the type of a concrete ChangeInfo
     * object - for instance, PostChangeInfo generally expects the commit message
     * to contain the `VP-Post-Title` tag. This is ensured by the ChangeInfoMatcher
     * and the `matchesCommitMessage()` implementation in every ChangeInfo type.
     *
     * @param CommitMessage $commitMessage
     * @return ChangeInfo
     */
    static function buildFromCommitMessage(CommitMessage $commitMessage);
}