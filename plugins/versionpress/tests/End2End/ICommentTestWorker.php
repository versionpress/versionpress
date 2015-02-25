<?php

namespace VersionPress\Tests\End2End;

interface ICommentTestWorker {
    public function prepare_createCommentAwaitingModeration();
    public function createCommentAwaitingModeration();
}