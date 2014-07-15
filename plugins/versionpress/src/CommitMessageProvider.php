<?php

class CommitMessageProvider {


    public function getCommitMessage(ChangeInfo $changeInfo) {
        $developerReadableMessage = $this->createDeveloperReadableMessage($changeInfo);
        $parsableMessage = $this->createParsableMessage($changeInfo);

        return new CommitMessage($developerReadableMessage, $parsableMessage);
    }

    private function createDeveloperReadableMessage(ChangeInfo $changeInfo) {
        if($changeInfo instanceof EntityChangeInfo) return $this->createDeveloperReadableMessageForEntity($changeInfo);
        return "Unknown change";
    }

    private function createParsableMessage(ChangeInfo $changeInfo) {
        if($changeInfo instanceof EntityChangeInfo) return $this->createParsableMessageForEntity($changeInfo);
        return "VP-Action: {$changeInfo->getObjectType()}/{$changeInfo->getAction()}";
    }


}