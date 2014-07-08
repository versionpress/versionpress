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
        $entityType = $changeInfo->entityType;
        $action = $changeInfo->type;
        $id = $changeInfo->entityId;

        $messageParts = array(
            'VP-Action' => "$entityType/$action",
            'VP-ID' => $id
        );

        $message = join("\n", array_map(function ($k, $v) {
                    return "$k: $v";
                }, array_keys($messageParts), array_values($messageParts)));
        return $message;
    }
}