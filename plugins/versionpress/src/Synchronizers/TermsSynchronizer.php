<?php

namespace VersionPress\Synchronizers;

use VersionPress\Utils\WordPressCacheUtils;

class TermsSynchronizer extends SynchronizerBase
{

    protected function transformEntities($entities)
    {
        $entities = parent::transformEntities($entities);
        foreach ($entities as $id => &$entity) {
            unset($entity['taxonomies']); // taxonomies are synchronized by TermTaxonomiesSynchronizer
        }
        return $entities;
    }

    protected function doEntitySpecificActions()
    {
        parent::doEntitySpecificActions();
        WordPressCacheUtils::clearTermCache(array_column($this->entities, 'vp_id'), $this->database);
    }
}
