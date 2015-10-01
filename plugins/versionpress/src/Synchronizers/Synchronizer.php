<?php

namespace VersionPress\Synchronizers;
/**
 * Synchronizers synchronize entities from {@link EntityStorage storages} back to the database.
 *
 * Every storage has its complementary synchronizer.
 *
 * Synchronizers do work that is kind of opposite to the ones of storages but with one major
 * difference: while storages usually add or delete entities one by one or by small amounts,
 * synchronizers operate over all entities and completely overwrite the whole db table
 * (with the exception of untracked or ignored rows, see below). Synchronizers also sometimes
 * execute additional SQL queries to get the database to a fully working state - for example,
 * the VersionPress\Synchronizers\PostsSynchronizer counts comments and updates the `comment_count` field.
 *
 * Synchronizers are run by the {@link VersionPress\Synchronizers\SynchronizationProcess}.
 */
interface Synchronizer {

    const SYNCHRONIZE_EVERYTHING = 'everything';

    /**
     * Synchronizes entities from storage to the database. It generally only works with tracked
     * entities, i.e. the ignored (untracked) rows in the database are left untouched. The rows
     * corresponding to tracked entities are usually in sync with the storage after this method
     * is done. It may happen that the synchronizer cannot synchronize everything in the first
     * pass. Because of this, the synchronize method takes a task for sychronization (usually
     * "everything" for the first pass) and returns a list of tasks that aren't done yet. It's
     * up to the SynchronizationProcess to call the synchronize method again with this tasks
     * when the previous pass is done.
     *
     * If the $entitiesToSynchronize is null, the synchronizer will synchronize all entities.
     * If it's an array, the synchronizer will synchronize only those entities.
     *
     * @param string $task
     * @param array $entitiesToSynchronize List of VPIDs and their possible parents {@see SynchronizationProcess::synchronize()}
     * @return string[]
     */
    function synchronize($task, $entitiesToSynchronize = null);
}
