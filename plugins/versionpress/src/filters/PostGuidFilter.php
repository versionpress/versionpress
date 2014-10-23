<?php

class PostGuidFilter implements EntityFilter {

    /**
     * Applies the filter on given entity
     *
     * @param array $entity
     * @return array
     */
    function apply($entity) {
        unset($entity['guid']);
        return $entity;
    }

    /**
     * Restores the entity (inverse function to apply)
     *
     * @param array $entity
     * @return array
     */
    function restore($entity) {
        if(isset($entity['ID'])) {
            $entity['guid'] = get_site_url() . '/?p=' . $entity['ID'];
        }
        return $entity;
    }
}