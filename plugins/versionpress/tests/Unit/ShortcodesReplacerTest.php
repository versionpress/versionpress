<?php

namespace VersionPress\Tests\Unit;

use VersionPress\Database\ShortcodesReplacer;

class ShortcodesReplacerTest extends \PHPUnit_Framework_TestCase {

    private $shortcodeSchemaValueMap = array(
        array('foo', array('id' => 'some-entity', 'ids' => 'another-entity')),
        array('bar', array('id' => 'some-entity')),
    );

    private $idValueMap = array(
        array('some-entity', '1', '123ABCD45EF'),
        array('another-entity', '1', '1234567890'),
        array('another-entity', '7', 'BOND007'),
    );

    private $shortcodesInfo;

    private $vpidRepository;

    protected function setUp() {
        include_once '../../../../ext-libs/wordpress/wp-includes/shortcodes.php';

        $this->shortcodesInfo = $this->getMockBuilder('VersionPress\Database\ShortcodesInfo')->disableOriginalConstructor()->getMock();
        $this->shortcodesInfo->expects($this->any())->method('getAllShortcodeNames')->will($this->returnValue(array_column($this->shortcodeSchemaValueMap, 0)));

        $this->shortcodesInfo->expects($this->any())->method('getShortcodeInfo')->will($this->returnValueMap($this->shortcodeSchemaValueMap));

        $this->vpidRepository = $this->getMockBuilder('VersionPress\Database\VpidRepository')->disableOriginalConstructor()->getMock();
        $this->vpidRepository->expects($this->any())->method('getVpidForEntity')->will($this->returnValueMap($this->idValueMap));

        $vpidValueMap = array_map(function ($idValueMapItem) { return array($idValueMapItem[2], $idValueMapItem[1]); }, $this->idValueMap);
        $this->vpidRepository->expects($this->any())->method('getIdForVpid')->will($this->returnValueMap($vpidValueMap));
    }

    /**
     * @test
     * @dataProvider shortcodeProvider
     */
    public function replacerReplacesIdsInKnownShortcodesWithVpids($input, $expectedOutput) {
        $shortcodesReplacer = new ShortcodesReplacer($this->shortcodesInfo, $this->vpidRepository);
        $replacedString = $shortcodesReplacer->replaceShortcodes($input);
        $this->assertEquals($expectedOutput, $replacedString);
    }

    /**
     * @test
     * @dataProvider shortcodeProvider
     */
    public function replacerRestoresIdsInKnownShortcodes($expectedOutput, $input) {
        $shortcodesReplacer = new ShortcodesReplacer($this->shortcodesInfo, $this->vpidRepository);
        $replacedString = $shortcodesReplacer->restoreShortcodes($input);
        $this->assertEquals($expectedOutput, $replacedString);
    }

    public function shortcodeProvider() {
        /** @var callable $ids */
        $ids = array($this, 'getVpidForId');

        return array(
            array('[foo id="1"]', '[foo id="' . $ids('some-entity', 1) . '"]'),
            array('[foo ids="1"]', '[foo ids="' . $ids('another-entity', 1) . '"]'),
            array('[foo ids="1,7"]', '[foo ids="' . $ids('another-entity', 1, 7) . '"]'),

            array('Some text [foo id="1"] with shortcode', 'Some text [foo id="' . $ids('some-entity', 1) . '"] with shortcode'),
            array('More [foo id="1"] shortcodes [bar id="1"]', 'More [foo id="' . $ids('some-entity', 1) . '"] shortcodes [bar id="' . $ids('some-entity', 1) . '"]'),
        );
    }

    public function getVpidForId($entity, $id1, $id2 = null) {
        $args = func_get_args();
        array_shift($args);

        $foundIds = array();
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