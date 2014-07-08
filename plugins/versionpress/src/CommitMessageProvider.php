<?php

class CommitMessageProvider {


    public function getCommitMessage(ChangeInfo $changeInfo) {
        $developerReadableMessage = $this->createDeveloperReadableMessage($changeInfo);
        $parsableMessage = $this->createParsableMessage($changeInfo);

        return new CommitMessage($developerReadableMessage, $parsableMessage);
    }

    private function createDeveloperReadableMessage(ChangeInfo $changeInfo) {
        static $verbs = array(
            'create' => 'Created',
            'edit' => 'Edited',
            'delete' => 'Deleted'
        );

        $formattedEntityId = preg_match("/\d/", $changeInfo->entityId) ? substr($changeInfo->entityId, 0, 4) : $changeInfo->entityId;
        return sprintf("%s %s '%s'", $verbs[$changeInfo->type], $changeInfo->entityType, $formattedEntityId);
    }

    private function createParsableMessage(ChangeInfo $changeInfo) {
        $action = $changeInfo->type;
        $id = $changeInfo->entityId;

        return "[$action][$id]";
    }
}