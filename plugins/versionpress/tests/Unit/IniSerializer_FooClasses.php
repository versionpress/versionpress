<?php


namespace VersionPress\Tests\Unit;

class IniSerializer_FooPrivate {
    private $attribute;

    public function __construct($attribute) {
        $this->attribute = $attribute;
    }
}

class IniSerializer_FooProtected {
    protected $attribute;

    public function __construct($attribute) {
        $this->attribute = $attribute;
    }
}


class IniSerializer_FooPublic {
    public $attribute;

    public function __construct($attribute) {
        $this->attribute = $attribute;
    }
}
