<?php
// @codingStandardsIgnoreFile

namespace VersionPress\Tests\Unit;

class IniSerializer_FooPrivate
{
    private $attribute;

    public function __construct($attribute)
    {
        $this->attribute = $attribute;
    }
}

class IniSerializer_FooProtected
{
    protected $attribute;

    public function __construct($attribute)
    {
        $this->attribute = $attribute;
    }
}


class IniSerializer_FooPublic
{
    public $attribute;

    public function __construct($attribute)
    {
        $this->attribute = $attribute;
    }
}

class IniSerializer_FooWithCleanup
{
    public $attribute;
    private $someCache;

    public function __construct($attribute)
    {
        $this->attribute = $attribute;
        $this->someCache = self::transformAttribute($attribute);
    }

    function __sleep()
    {
        return ['attribute'];
    }

    function __wakeup()
    {
        $this->someCache = self::transformAttribute($this->attribute);
    }

    private static function transformAttribute($attribute)
    {
        return 'cached attribute: ' . $attribute;
    }
}
