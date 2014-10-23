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
        $modifiedFiles = Git::getModifiedFiles(sprintf("%s~1..%s", $commitHash, $commitHash));
        $affectedPosts = $this->getAffectedPosts($modifiedFiles);

        $this->updateChangeDateForPosts($affectedPosts);

        if(!Git::revert($commitHash)) return RevertStatus::FAILED;

        $this->synchronize();

        $changeInfo = new RevertChangeInfo(RevertChangeInfo::ACTION_UNDO, $commitHash);
        $this->committer->forceChangeInfo($changeInfo);
        $this->committer->commit();

        return RevertStatus::OK;
    }

    public function revertAll($commitHash) {
        $modifiedFiles = Git::getModifiedFiles($commitHash);
        $affectedPosts = $this->getAffectedPosts($modifiedFiles);

        $this->updateChangeDateForPosts($affectedPosts);

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

    private function updateChangeDateForPosts($vpIds) {
        $date = current_time('mysql');
        $dateGmt = current_time('mysql', true);
        foreach ($vpIds as $vpId) {
            $sql = "update {$this->database->prefix}posts set post_modified = '{$date}', post_modified_gmt = '{$dateGmt}' where ID = (select id from {$this->database->prefix}vp_id where vp_id = unhex('{$vpId}'))";
            $this->database->query($sql);
        }
    }

    private function synchronize() {
        $synchronizationQueue = array('options', 'users', 'usermeta', 'posts', 'comments', 'terms', 'term_taxonomy', 'term_relationships');
        $this->synchronizationProcess->synchronize($synchronizationQueue);
    }

    private function getAffectedPosts($modifiedFiles) {
        $posts = array();

        foreach($modifiedFiles as $filename) {
            $match = NStrings::match($filename, "~/posts/(.*)\.ini~");
            if($match) {
                $posts[] = $match[1];
            }
        }

        return $posts;
    }
}