<?php

namespace VersionPress\Tests\Unit;

use org\bovigo\vfs\vfsStream;
use Symfony\Component\Yaml\Yaml;
use VersionPress\Actions\ActionsInfo;
use VersionPress\Actions\ActionsInfoProvider;
use VersionPress\Tests\Utils\HookMock;

class ActionsInfoTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        HookMock::setUp();
    }

    /**
     * @test
     */
    public function actionsInfoCreatesDescriptionForGivenAction()
    {
        $actions = [
            'some-action' => ['message' => 'Some description'],
            'other-action' => ['message' => 'Other description'],
        ];

        $actionsInfo = new ActionsInfo('some-scope', $actions);
        $description1 = $actionsInfo->getDescription('some-action', 'some-id', []);
        $description2 = $actionsInfo->getDescription('other-action', 'some-id', []);

        $this->assertSame('Some description', $description1);
        $this->assertSame('Other description', $description2);
    }

    /**
     * @test
     */
    public function actionsInfoReplacesTagPlaceholderWithItsValue()
    {
        $tags = ['VP-Tag' => '/'];
        $actions = ['some-action' => ['message' => 'Some description containing %VP-Tag%']];


        $actionsInfo = new ActionsInfo('some-scope', $actions, $tags);

        $description = $actionsInfo->getDescription('some-action', 'some-id', ['VP-Tag' => 'tag value']);
        $this->assertSame('Some description containing tag value', $description);
    }

    /**
     * @test
     */
    public function actionsInfoReplacesVpidPlaceholderWithVpid()
    {
        $actions = ['some-action' => ['message' => 'Some description containing %VPID%']];

        $actionsInfo = new ActionsInfo('some-scope', $actions);
        $description = $actionsInfo->getDescription('some-action', 'some-id', []);
        $this->assertSame('Some description containing some-id', $description);
    }

    /**
     * @test
     */
    public function actionsInfoAppliesFilterOnDescription()
    {
        $actions = ['some-action' => ['message' => 'Some description']];

        add_filter('vp_action_description_some-scope', function ($description) {
            $this->assertSame('Some description', $description);
            return 'Filtered description';
        });

        $actionsInfo = new ActionsInfo('some-scope', $actions);
        $description = $actionsInfo->getDescription('some-action', 'some-id', []);

        $this->assertSame('Filtered description', $description);
    }
}
