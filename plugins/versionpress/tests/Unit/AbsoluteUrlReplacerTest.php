<?php

namespace VersionPress\Tests\Unit;

use VersionPress\Utils\AbsoluteUrlReplacer;

class AbsoluteUrlReplacerTest extends \PHPUnit_Framework_TestCase {

    const REPLACED_URL = 'http://wp.example.com/';

    /** @var \VersionPress\Utils\AbsoluteUrlReplacer */
    private $filter;

    protected function setUp() {
        parent::setUp();
        $this->filter = new AbsoluteUrlReplacer(self::REPLACED_URL);
    }

    /**
     * @test
     * @dataProvider entityDataProvider
     *
     * @param $entity
     * @param $entityWithReplacedUrls
     */
    public function itReplacesUrlWithPlaceholder($entity, $entityWithReplacedUrls) {
        $result = $this->filter->replace($entity);
        $this->assertEquals($entityWithReplacedUrls, $result);
    }

    /**
     * @test
     * @dataProvider entityDataProvider
     *
     * @param $entity
     * @param $entityWithReplacedUrls
     */
    public function itRestoresUrl($entity, $entityWithReplacedUrls) {
        $result = $this->filter->restore($entityWithReplacedUrls);
        $this->assertEquals($entity, $result);
    }

    public function entityDataProvider() {

        $simpleObject = new \stdClass();
        $simpleObject->someUrl = self::REPLACED_URL;

        $replacedSimpleObject = new \stdClass();
        $replacedSimpleObject->someUrl = AbsoluteUrlReplacer::PLACEHOLDER;


        $complexObject = new \stdClass();
        $complexObject->props = array('some' . self::REPLACED_URL . 'url');
        $complexObject->obj = new \stdClass();
        $complexObject->obj->url = self::REPLACED_URL;

        $replacedComplexObject = new \stdClass();
        $replacedComplexObject->props = array('some' . AbsoluteUrlReplacer::PLACEHOLDER . 'url');
        $replacedComplexObject->obj = new \stdClass();
        $replacedComplexObject->obj->url = AbsoluteUrlReplacer::PLACEHOLDER;


        return array(
            array(
                array('url' => self::REPLACED_URL),
                array('url' => AbsoluteUrlReplacer::PLACEHOLDER)
            ),
            array(
                array('url' => self::REPLACED_URL, 'foo' => self::REPLACED_URL),
                array('url' => AbsoluteUrlReplacer::PLACEHOLDER, 'foo' => AbsoluteUrlReplacer::PLACEHOLDER)
            ),
            array(
                array('url' => self::REPLACED_URL, 'guid' => self::REPLACED_URL), // guid should not be replaced
                array('url' => AbsoluteUrlReplacer::PLACEHOLDER, 'guid' => self::REPLACED_URL)
            ),
            array(
                array('url' => 'some string with url ' . self::REPLACED_URL . ' inside'),
                array('url' => 'some string with url ' . AbsoluteUrlReplacer::PLACEHOLDER . ' inside')
            ),

            array(
                array('url' => serialize('some serialized string with url ' . self::REPLACED_URL . ' inside')),
                array('url' => serialize('some serialized string with url ' . AbsoluteUrlReplacer::PLACEHOLDER . ' inside'))
            ),
            array(
                array('url' => serialize(array('some serialized array with url ', self::REPLACED_URL, 'inside'))),
                array('url' => serialize(array('some serialized array with url ', AbsoluteUrlReplacer::PLACEHOLDER, 'inside')))
            ),
            array(
                array('obj' => serialize($simpleObject)),
                array('obj' => serialize($replacedSimpleObject))
            ),
            array(
                array('obj' => serialize($complexObject)),
                array('obj' => serialize($replacedComplexObject))
            ),
        );
    }
}