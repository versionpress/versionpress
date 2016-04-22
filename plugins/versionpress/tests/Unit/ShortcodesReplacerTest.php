<?php

namespace VersionPress\Tests\Unit;

use VersionPress\Database\ShortcodesInfo;
use VersionPress\Database\ShortcodesReplacer;
use VersionPress\Database\VpidRepository;

class ShortcodesReplacerTest extends \PHPUnit_Framework_TestCase
{

    private $shortcodeSchemaValueMap = [
        ['foo', ['id' => 'some-entity', 'ids' => 'another-entity']],
        ['bar', ['id' => 'some-entity']],
    ];

    private $idValueMap = [
        ['some-entity', '1', '123ABCD45EF'],
        ['another-entity', '1', '1234567890'],
        ['another-entity', '7', 'BOND007'],
    ];

    private $postShortcodesSchema = ["post" => ["post_content"]];

    private $shortcodesInfo;

    private $vpidRepository;

    protected function setUp()
    {
        if (!function_exists('get_shortcode_regex')) {
            include_once __DIR__ . '/../../../../ext-libs/wordpress/wp-includes/shortcodes.php';
        }

        $this->shortcodesInfo = $this->getMockBuilder(ShortcodesInfo::class)
            ->disableOriginalConstructor()->getMock();
        $this->shortcodesInfo->expects($this->any())
            ->method('getAllShortcodeNames')
            ->will($this->returnValue(array_column($this->shortcodeSchemaValueMap, 0)));

        $this->shortcodesInfo->expects($this->any())
            ->method('getShortcodeInfo')
            ->will($this->returnValueMap($this->shortcodeSchemaValueMap));

        $this->shortcodesInfo->expects($this->any())
            ->method('getShortcodeLocations')
            ->will($this->returnValue($this->postShortcodesSchema));

        $this->vpidRepository = $this->getMockBuilder(VpidRepository::class)
            ->disableOriginalConstructor()->getMock();
        $this->vpidRepository->expects($this->any())
            ->method('getVpidForEntity')
            ->will($this->returnValueMap($this->idValueMap));

        $vpidValueMap = array_map(function ($idValueMapItem) {
            return [$idValueMapItem[2], $idValueMapItem[1]];
        }, $this->idValueMap);

        $this->vpidRepository->expects($this->any())
            ->method('getIdForVpid')
            ->will($this->returnValueMap($vpidValueMap));


    }

    /**
     * @test
     * @dataProvider shortcodeProvider
     */
    public function replacerReplacesIdForVpidsInEntity($input, $expectedReplacedOutput)
    {
        $shortcodesReplacer = new ShortcodesReplacer($this->shortcodesInfo, $this->vpidRepository);
        $testingPost = [
            'post_title' => $input,
            'post_content' => $input
        ];
        $expectedPost = [
            'post_title' => $input,
            'post_content' => $expectedReplacedOutput
        ];
        $entityWithReplacedShortCodes = $shortcodesReplacer->replaceShortcodesInEntity('post', $testingPost);
        $this->assertEquals($expectedPost, $entityWithReplacedShortCodes);
    }

    /**
     * @test
     * @dataProvider shortcodeProvider
     */
    public function replacerRestoresIdsFromVpidsInEntity($expectedReplacedOutput, $input)
    {
        $shortcodesReplacer = new ShortcodesReplacer($this->shortcodesInfo, $this->vpidRepository);
        $testingPost = [
            'post_title' => $input,
            'post_content' => $input
        ];
        $expectedPost = [
            'post_title' => $input,
            'post_content' => $expectedReplacedOutput
        ];
        $entityWithReplacedShortCodes = $shortcodesReplacer->restoreShortcodesInEntity('post', $testingPost);
        $this->assertEquals($expectedPost, $entityWithReplacedShortCodes);
    }

    public function shortcodeProvider()
    {
        /** @var callable $ids */
        $ids = [$this, 'getVpidForId'];

        return [
            ['[foo id="1"]', '[foo id="' . $ids('some-entity', 1) . '"]'],
            ['[foo ids="1"]', '[foo ids="' . $ids('another-entity', 1) . '"]'],
            ['[foo ids="1,7"]', '[foo ids="' . $ids('another-entity', 1, 7) . '"]'],

            [
                'Some text [foo id="1"] with shortcode',
                'Some text [foo id="' . $ids('some-entity', 1) . '"] with shortcode'
            ],
            [
                'More [foo id="1"] shortcodes [bar id="1"]',
                'More [foo id="' . $ids('some-entity', 1) . '"] shortcodes [bar id="' . $ids('some-entity', 1) . '"]'
            ],
        ];
    }

    public function getVpidForId($entity, $id1, $id2 = null)
    {
        $args = func_get_args();
        array_shift($args);

        $foundIds = [];
        foreach ($args as $id) {
            foreach ($this->idValueMap as $idValueMapItem) {
                if ($idValueMapItem[0] === $entity && $idValueMapItem[1] == $id) {
                    $foundIds[] = $idValueMapItem[2];
                    continue 2;
                }
            }

            $foundIds[] = $id; // not found
        }

        return join(',', $foundIds);
    }
}
