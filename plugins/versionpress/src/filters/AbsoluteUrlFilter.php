<?php

class AbsoluteUrlFilter implements EntityFilter {

    const PLACEHOLDER = "<<[site-url]>>";
    private $siteUrl;

    function __construct() {
        $this->siteUrl = get_site_url();
    }

    /**
     * Replaces absolute URLs with placeholder
     *
     * @param array $entity
     * @return array
     */
    function apply($entity) {
        foreach ($entity as $field => $value) {
            if($field === "guid") continue; // guids cannot be changed even they are in form of URL
            if(isset($entity[$field])) {
                $entity[$field] = $this->replaceLocalUrls($value);
            }
        }
        return $entity;
    }

    /**
     * Replaces the placeholder with absolute URL
     *
     * @param array $entity
     * @return array
     */
    function restore($entity) {
        foreach ($entity as $field => $value) {
            if(isset($entity[$field])) {
                $entity[$field] = $this->replacePlaceholders($value);
            }
        }
        return $entity;
    }

    private function replaceLocalUrls($value) {
        return str_replace($this->siteUrl, self::PLACEHOLDER, $value);
    }

    private function replacePlaceholders($value) {
        return str_replace(self::PLACEHOLDER, $this->siteUrl, $value);
    }
}