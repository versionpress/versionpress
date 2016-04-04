<?php

namespace VersionPress\Utils;

/**
 * Class for manipulation with
 */
class Cursor {

    private $data;
    private $path;

    private $isReferenceSet;
    private $ref;

    public function __construct(&$data, $path) {
        $this->data = &$data;
        $this->path = $path;
    }

    public function getValue() {
        $this->ensureReference();
        return $this->ref;
    }

    public function setValue($value) {
        $this->ensureReference();
        $this->ref = $value;
    }

    private function ensureReference() {
        if (!$this->isReferenceSet) {
            $this->setReference();
            $this->isReferenceSet = true;
        }
    }

    private function setReference() {
        $this->ref = &$this->data;
        foreach ($this->path as $subItem) {
            if (is_array($this->ref)) {
                $this->ref = &$this->ref[$subItem];
            } else if (is_object($this->ref)) {
                $this->ref = &$this->ref->{$subItem};
            }
        }
    }
}
