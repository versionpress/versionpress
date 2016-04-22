<?php


namespace VersionPress\Utils;

/**
 * Assorted helper functions for workflow functionality (cloning, merging, ...)
 * for now. This will probably be refactored into its own namespace and better
 * structured classes one day.
 */
class WorkflowUtils {

    public static function isCloneNameValid($cloneName) {
        return preg_match('/^[a-zA-Z0-9-_]+$/', $cloneName) === 1;
    }

}
