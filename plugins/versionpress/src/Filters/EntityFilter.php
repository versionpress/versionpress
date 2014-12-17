<?php

namespace VersionPress\Filters;

interface EntityFilter {
    /**
     * Applies the filter on given entity
     *
     * @param array $entity
     * @return array
     */
    function apply($entity);

    /**
     * Restores the entity (inverse function to apply)
     *
     * @param array $entity
     * @return array
     */
    function restore($entity);
}