<?php

namespace VersionPress\Tests\End2End;

interface ICommentTestPerformer {
    public function prepare_createCommentAwaitingModeration();
    public function createCommentAwaitingModeration();
}