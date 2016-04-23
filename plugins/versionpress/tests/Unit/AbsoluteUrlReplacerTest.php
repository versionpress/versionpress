<?php

namespace VersionPress\Tests\Unit;

use VersionPress\Utils\AbsoluteUrlReplacer;

class AbsoluteUrlReplacerTest extends \PHPUnit_Framework_TestCase
{

    const REPLACED_URL = 'http://wp.example.com/';

    /** @var \VersionPress\Utils\AbsoluteUrlReplacer */
    private $filter;

    protected function setUp()
    {
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
    public function itReplacesUrlWithPlaceholder($entity, $entityWithReplacedUrls)
    {
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
    public function itRestoresUrl($entity, $entityWithReplacedUrls)
    {
        $result = $this->filter->restore($entityWithReplacedUrls);
        $this->assertEquals($entity, $result);
    }

    public function entityDataProvider()
    {

        $simpleObject = new \stdClass();
        $simpleObject->someUrl = self::REPLACED_URL;

        $replacedSimpleObject = new \stdClass();
        $replacedSimpleObject->someUrl = AbsoluteUrlReplacer::PLACEHOLDER;


        $complexObject = new \stdClass();
        $complexObject->props = ['some' . self::REPLACED_URL . 'url'];
        $complexObject->obj = new \stdClass();
        $complexObject->obj->url = self::REPLACED_URL;

        $replacedComplexObject = new \stdClass();
        $replacedComplexObject->props = ['some' . AbsoluteUrlReplacer::PLACEHOLDER . 'url'];
        $replacedComplexObject->obj = new \stdClass();
        $replacedComplexObject->obj->url = AbsoluteUrlReplacer::PLACEHOLDER;


        $objectWithCyclicReference = new \stdClass();
        $objectWithCyclicReference->cyclicReference = $objectWithCyclicReference;
        $objectWithCyclicReference->someUrl = self::REPLACED_URL;

        $replacedObjectWithCyclicReference = new \stdClass();
        $replacedObjectWithCyclicReference->cyclicReference = $replacedObjectWithCyclicReference;
        $replacedObjectWithCyclicReference->someUrl = AbsoluteUrlReplacer::PLACEHOLDER;

        return [
            [
                ['url' => self::REPLACED_URL],
                ['url' => AbsoluteUrlReplacer::PLACEHOLDER]
            ],
            [
                ['url' => self::REPLACED_URL, 'foo' => self::REPLACED_URL],
                ['url' => AbsoluteUrlReplacer::PLACEHOLDER, 'foo' => AbsoluteUrlReplacer::PLACEHOLDER]
            ],
            [
                ['url' => self::REPLACED_URL, 'guid' => self::REPLACED_URL], // guid should not be replaced
                ['url' => AbsoluteUrlReplacer::PLACEHOLDER, 'guid' => self::REPLACED_URL]
            ],
            [
                ['url' => 'some string with url ' . self::REPLACED_URL . ' inside'],
                ['url' => 'some string with url ' . AbsoluteUrlReplacer::PLACEHOLDER . ' inside']
            ],

            [
                ['url' => serialize('some serialized string with url ' . self::REPLACED_URL . ' inside')],
                ['url' => serialize('some serialized string with url ' . AbsoluteUrlReplacer::PLACEHOLDER . ' inside')]
            ],
            [
                ['url' => serialize(['some serialized array with url ', self::REPLACED_URL, 'inside'])],
                [
                    'url' => serialize([
                        'some serialized array with url ',
                        AbsoluteUrlReplacer::PLACEHOLDER,
                        'inside'
                    ])
                ]
            ],
            [
                ['obj' => serialize($simpleObject)],
                ['obj' => serialize($replacedSimpleObject)]
            ],
            [
                ['obj' => serialize($complexObject)],
                ['obj' => serialize($replacedComplexObject)]
            ],
            [
                ['obj' => serialize($objectWithCyclicReference)],
                ['obj' => serialize($replacedObjectWithCyclicReference)]
            ],
        ];
    }
}
