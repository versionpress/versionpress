<?php

/**
 * Synchronizers synchronize entities from {@link EntityStorage storages} back to the database.
 *
 * Most storages have their complementary synchronizers so the typical relationship is 1:1 but in some
 * cases, there may be more synchronizers for a single storage. For example, both PostsSynchronizer
 * and TermRelationshipsSynchronizer exist for PostStorage.
 *
 * Synchronizers do work that is kind of opposite to the ones of storages but with one major
 * difference: while storages usually add or delete entities one by one or by small amounts,
 * synchronizers operate over all entities and completely overwrite the whole db table
 * (with the exception of untracked or ignored rows, see below). Synchronizers also sometimes
 * execute additional SQL queries to get the database to a fully working state - for example,
 * the PostsSynchronizer counts comments and updates the `comment_count` field.
 *
 * Synchronizers are run by the {@link SynchronizationProcess}.
 */
interface Synchronizer {

    /**
     * Synchronizes entities from storage to the database. It generally only works with tracked
     * entities, i.e. the ignored (untracked) rows in the database are left untouched. The rows
     * corresponding to tracked entities are in sync with the storage after this method is done.
     */
    function synchronize();
}
