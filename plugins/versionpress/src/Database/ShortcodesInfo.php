<?php

namespace VersionPress\Database;

use Nette\Neon\Neon;

class ShortcodesInfo {

    public function __construct($shortcodeFile) {
        $this->shortcodeSchema = Neon::decode(file_get_contents($shortcodeFile));
    }

    public function getAllShortcodeNames() {
        return array_keys($this->shortcodeSchema['shortcodes']);
    }

    public function getShortcodeInfo($shortcodeName) {
        return $this->shortcodeSchema['shortcodes'][$shortcodeName];
    }

    public function getShortcodeLocations() {
        return $this->shortcodeSchema['shortcode-locations'];
    }
}
