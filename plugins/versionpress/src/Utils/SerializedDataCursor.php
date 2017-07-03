<?php

namespace VersionPress\Utils;

/**
 * Class for manipulation with data in serialized hierarchic structures.
 * It enabled to create a "pointer" to some nested value, read it and change it.
 * It supports nested serialized data, e.g. string in serialized object in serialized array.
 * I <3 WP
 *
 */
class SerializedDataCursor
{

    private $data;
    private $paths;

    public function __construct(&$data, $paths)
    {
        $this->data = &$data;
        $this->paths = $paths;
    }

    public function getValue()
    {
        $value = $this->data;

        foreach ($this->paths as $path) {
            $value = unserialize($value);
            $cursor = new Cursor($value, $path);
            $value = $cursor->getValue();
        }

        return $value;
    }

    public function setValue($value)
    {
        /** @var Cursor[] $levels */
        $levels = [];

        $currentLevelValue = $this->data;
        $levelValues = [];
        foreach ($this->paths as $level => $path) {
            $levelValues[$level] = unserialize($currentLevelValue);
            $cursor = new Cursor($levelValues[$level], $path);
            $levels[] = [&$levelValues[$level], $cursor];
            $currentLevelValue = $cursor->getValue();
        }

        /** @var Cursor[] $levels */
        $levels = array_reverse($levels);

        $currentLevelValue = $value;
        foreach ($levels as $level) {
            /** @var Cursor $cursor */
            $cursor = $level[1];
            $cursor->setValue($currentLevelValue);
            $currentLevelValue = serialize($level[0]);
        }

        $this->data = $currentLevelValue;
    }
}
