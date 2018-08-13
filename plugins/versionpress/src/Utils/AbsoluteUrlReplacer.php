<?php

namespace VersionPress\Utils;

/**
 * Replaces absolute site URL with placeholder
 */
class AbsoluteUrlReplacer
{

    const PLACEHOLDER = "<<[site-url]>>";
    private $siteUrl;
    private $replacedObjects = [];

    public function __construct($siteUrl)
    {
        $this->siteUrl = $siteUrl;
    }

    /**
     * Replaces absolute URLs with placeholder
     *
     * @param array $entity
     * @return array
     */
    public function replace($entity)
    {
        $this->replacedObjects = [];

        foreach ($entity as $field => $value) {
            if ($field === "guid") {
                continue;
            } // guids cannot be changed even they are in form of URL
            if (isset($entity[$field])) {
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
    public function restore($entity)
    {
        $this->replacedObjects = [];

        foreach ($entity as $field => $value) {
            if (isset($entity[$field])) {
                $entity[$field] = $this->replacePlaceholders($value);
            }
        }
        return $entity;
    }

    private function replaceLocalUrls($value)
    {
        if (StringUtils::isSerializedValue($value)) {
            $unserializedValue = unserialize($value);
            $replacedValue = $this->replaceRecursively($unserializedValue, [$this, 'replaceLocalUrls']);
            return serialize($replacedValue);
        } else {
            return is_string($value) ? str_replace($this->siteUrl, self::PLACEHOLDER, $value) : $value;
        }
    }

    private function replacePlaceholders($value)
    {
        if (StringUtils::isSerializedValue($value)) {
            $unserializedValue = unserialize($value);
            $replacedValue = $this->replaceRecursively($unserializedValue, [$this, 'replacePlaceholders']);
            return serialize($replacedValue);
        } else {
            return is_string($value) ? str_replace(self::PLACEHOLDER, $this->siteUrl, $value) : $value;
        }
    }

    /**
     * @param mixed $value Can be string, array or even an object or all this combined
     * @param callable $replaceFn Takes one parameter - the "haystack" and return replaced string.
     * @return string
     */
    private function replaceRecursively($value, $replaceFn)
    {
        if (is_string($value)) {
            return call_user_func($replaceFn, $value);
        } else {
            if (is_array($value)) {
                $tmp = [];
                foreach ($value as $key => $arrayValue) {
                    $tmp[$key] = $this->replaceRecursively($arrayValue, $replaceFn);
                }
                return $tmp;
            } else {
                if (is_object($value) && !in_array(spl_object_hash($value), $this->replacedObjects)) {
                    $this->replacedObjects[] = spl_object_hash($value); // protection against cyclic references

                    $r = new \ReflectionObject($value);
                    $p = $r->getProperties();
                    if (get_class($value) != 'wpdb' && get_class($value) != 'mysqli' && get_class($value) != 'mysqli_result') {
                        foreach ($p as $prop) {
                            $prop->setAccessible(true);
                            $newValue = $this->replaceRecursively($prop->getValue($value), $replaceFn);
                            if (get_class($newValue) != 'wpdb' && get_class($newValue) != 'mysqli' && get_class($newValue) != 'mysqli_result') {
                                $prop->setValue($value, $newValue);
                            }
                        }
                    }
                    return $value;
                } else {
                    return $value;
                }
            }
        }
    }
}
