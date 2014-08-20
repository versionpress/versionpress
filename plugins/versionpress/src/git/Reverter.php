<?php

class Reverter {

    /**
     * @var SynchronizationProcess
     */
    private $synchronizationProcess;
    /**
     * @var wpdb
     */
    private $database;
    /**
     * @var Committer
     */
    private $committer;

    public function __construct(SynchronizationProcess $synchronizationProcess, wpdb $database, Committer $committer) {
        $this->synchronizationProcess = $synchronizationProcess;
        $this->database = $database;
        $this->committer = $committer;
    }

    public function revert($commitHash) {
        if(!Git::revert($commitHash)) return RevertStatus::FAILED;

        $this->synchronize();

        $changeInfo = new RevertChangeInfo(RevertChangeInfo::ACTION_UNDO, $commitHash);
        $this->committer->forceChangeInfo($changeInfo);
        $this->committer->commit();

        return RevertStatus::OK;
    }

    public function revertAll($commitHash) {
        Git::revertAll($commitHash);

        if(!Git::willCommit()) {
            return RevertStatus::NOTHING_TO_COMMIT;
        }

        $this->synchronize();

        $changeInfo = new RevertChangeInfo(RevertChangeInfo::ACTION_ROLLBACK, $commitHash);
        $this->committer->forceChangeInfo($changeInfo);
        $this->committer->commit();
        return RevertStatus::OK;
    }

    private function fixCommentCount() {
        $sql = "update {$this->database->prefix}posts set comment_count =
     (select count(*) from {$this->database->prefix}comments where comment_post_ID = {$this->database->prefix}posts.ID and comment_approved = 1);";
        $this->database->query($sql);
    }

    private function synchronize() {
        $synchronizationQueue = array('options', 'users', 'usermeta', 'posts', 'comments', 'terms', 'term_taxonomy', 'term_relationships');
        $this->synchronizationProcess->synchronize($synchronizationQueue);
        $this->fixCommentCount();
    }
}