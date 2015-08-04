<?php

namespace VersionPress\Tests\Unit;

use VersionPress\Filters\AbsoluteUrlFilter;

class AbsoluteUrlFilterTest extends \PHPUnit_Framework_TestCase {

    const REPLACED_URL = 'http://wp.example.com/';

    /** @var AbsoluteUrlFilter */
    private $filter;

    protected function setUp() {
        parent::setUp();
        $this->filter = new AbsoluteUrlFilter(self::REPLACED_URL);
    }

    /**
     * @test
     * @dataProvider entityDataProvider
     *
     * @param $entity
     * @param $entityWithReplacedUrls
     */
    public function filterReplacesUrlWithPlaceholder($entity, $entityWithReplacedUrls) {
        $result = $this->filter->apply($entity);
        $this->assertEquals($entityWithReplacedUrls, $result);
    }

    /**
     * @test
     * @dataProvider entityDataProvider
     *
     * @param $entity
     * @param $entityWithReplacedUrls
     */
    public function filterRestoresUrl($entity, $entityWithReplacedUrls) {
        $result = $this->filter->restore($entityWithReplacedUrls);
        $this->assertEquals($entity, $result);
    }

    public function entityDataProvider() {

        $simpleObject = new \stdClass();
        $simpleObject->someUrl = self::REPLACED_URL;

        $replacedSimpleObject = new \stdClass();
        $replacedSimpleObject->someUrl = AbsoluteUrlFilter::PLACEHOLDER;


        $complexObject = new \stdClass();
        $complexObject->props = array('some' . self::REPLACED_URL . 'url');
        $complexObject->obj = new \stdClass();
        $complexObject->obj->url = self::REPLACED_URL;

        $replacedComplexObject = new \stdClass();
        $replacedComplexObject->props = array('some' . AbsoluteUrlFilter::PLACEHOLDER . 'url');
        $replacedComplexObject->obj = new \stdClass();
        $replacedComplexObject->obj->url = AbsoluteUrlFilter::PLACEHOLDER;


        return array(
            array(
                array('url' => self::REPLACED_URL),
                array('url' => AbsoluteUrlFilter::PLACEHOLDER)
            ),
            array(
                array('url' => self::REPLACED_URL, 'foo' => self::REPLACED_URL),
                array('url' => AbsoluteUrlFilter::PLACEHOLDER, 'foo' => AbsoluteUrlFilter::PLACEHOLDER)
            ),
            array(
                array('url' => self::REPLACED_URL, 'guid' => self::REPLACED_URL), // guid should not be replaced
                array('url' => AbsoluteUrlFilter::PLACEHOLDER, 'guid' => self::REPLACED_URL)
            ),
            array(
                array('url' => 'some string with url ' . self::REPLACED_URL . ' inside'),
                array('url' => 'some string with url ' . AbsoluteUrlFilter::PLACEHOLDER . ' inside')
            ),

            array(
                array('url' => serialize('some serialized string with url ' . self::REPLACED_URL . ' inside')),
                array('url' => serialize('some serialized string with url ' . AbsoluteUrlFilter::PLACEHOLDER . ' inside'))
            ),
            array(
                array('url' => serialize(array('some serialized array with url ', self::REPLACED_URL, 'inside'))),
                array('url' => serialize(array('some serialized array with url ', AbsoluteUrlFilter::PLACEHOLDER, 'inside')))
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