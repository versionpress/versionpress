<?php

namespace VersionPress\Git;

class RevertStatus {

    const OK = 'ok';
    const MERGE_CONFLICT = 'merge-conflict';
    const VIOLATED_REFERENTIAL_INTEGRITY = 'violated-referential-integrity';
    const NOTHING_TO_COMMIT = 'nothing-to-commit';
    const NOT_CLEAN_WORKING_DIRECTORY = 'not-clean-working-directory';
}