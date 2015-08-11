<?php

namespace VersionPress\Utils;
use Tracy\Debugger;

/**
 * Replaces absolute site URL with placeholder
 */
class AbsoluteUrlReplacer {

    const PLACEHOLDER = "<<[site-url]>>";
    private $siteUrl;

    public function __construct($siteUrl) {
        $this->siteUrl = $siteUrl;
    }

    /**
     * Replaces absolute URLs with placeholder
     *
     * @param array $entity
     * @return array
     */
    public function replace($entity) {
        foreach ($entity as $field => $value) {
            if ($field === "guid") continue; // guids cannot be changed even they are in form of URL
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
    public function restore($entity) {
        foreach ($entity as $field => $value) {
            if (isset($entity[$field])) {
                $entity[$field] = $this->replacePlaceholders($value);
            }
        }
        return $entity;
    }

    private function replaceLocalUrls($value) {
        if ($this->isSerializedValue($value)) {
            $unserializedValue = unserialize($value);
            $replacedValue = $this->replaceRecursively($unserializedValue, array($this, 'replaceLocalUrls'));
            return serialize($replacedValue);
        } else {
            return is_string($value) ? str_replace($this->siteUrl, self::PLACEHOLDER, $value) : $value;
        }
    }

    private function replacePlaceholders($value) {
        if ($this->isSerializedValue($value)) {
            $unserializedValue = unserialize($value);
            $replacedValue = $this->replaceRecursively($unserializedValue, array($this, 'replacePlaceholders'));
            return serialize($replacedValue);
        } else {
            return is_string($value) ? str_replace(self::PLACEHOLDER, $this->siteUrl, $value) : $value;
        }
    }

    private function isSerializedValue($value) {
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        $test = @unserialize(($value)); // it throws an error and returns false if $value is not a serialized object
        return $test !== false || $value === 'b:0;';
    }

    /**
     * @param mixed $value Can be string, array or even an object or all this combined
     * @param callable $replaceFn Takes one parameter - the "haystack" and return replaced string.
     * @return string
     */
    private function replaceRecursively($value, $replaceFn) {
        if (is_string($value)) {
            return call_user_func($replaceFn, $value);
        } else if (is_array($value)) {
            $tmp = array();
            foreach ($value as $key => $arrayValue) {
                $tmp[$key] = $this->replaceRecursively($arrayValue, $replaceFn);
            }
            return $tmp;
        } else if (is_object($value)) {
            $r = new \ReflectionObject($value);
            $p = $r->getProperties();
            foreach ($p as $prop) {
                $prop->setAccessible(true);
                $prop->setValue($value, $this->replaceRecursively($prop->getValue($value), $replaceFn));
            }
            return $value;
        } else {
            return $value;
        }
    }
}
