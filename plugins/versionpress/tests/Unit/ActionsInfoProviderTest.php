<?php

namespace VersionPress\Tests\Unit;

use org\bovigo\vfs\vfsStream;
use Symfony\Component\Yaml\Yaml;
use VersionPress\Actions\ActionsInfo;
use VersionPress\Actions\ActionsInfoProvider;
use VersionPress\Tests\Utils\HookMock;

class ActionsInfoProviderTest extends \PHPUnit_Framework_TestCase
{
    private $root;

    protected function setUp()
    {
        $this->root = vfsStream::setup();
        HookMock::setUp();
    }

    /**
     * @test
     * @dataProvider scopeDefinitionProvider
     */
    public function actionsInfoProviderCreatesActionInfo($scopeDefinition, $description, $tags, $priority, $parentIdTag)
    {
        $actionsInfoProvider = new ActionsInfoProvider([$scopeDefinition]);
        $actionsInfo = $actionsInfoProvider->getActionsInfo('some-scope');

        $this->assertInstanceOf(ActionsInfo::class, $actionsInfo);
        $this->assertSame($description, $actionsInfo->getDescription('some-action', 'some-id', []));
        $this->assertSame($tags, $actionsInfo->getTags());
        $this->assertSame($priority, $actionsInfo->getActionPriority('some-action'));
        $this->assertSame($parentIdTag, $actionsInfo->getTagContainingParentId());
    }

    public function scopeDefinitionProvider()
    {
        $description = 'Some description';
        $simpleScopeDefinition = [
            'some-scope' => [
                'actions' => ['some-action' => $description]
            ]
        ];

        $tags = ['VP-Tag' => 'some_field'];
        $customPriority = 13;
        $parentIdTag = 'VP-Tag';
        $complexScopeDefinition = [
            'some-scope' => [
                'tags' => $tags,
                'actions' => ['some-action' => ['message' => 'Some description', 'priority' => $customPriority]],
                'parent-id-tag' => $parentIdTag,
            ]
        ];

        return [
            [$simpleScopeDefinition, $description, [], ActionsInfoProvider::DEFAULT_PRIORITY, null],
            [$complexScopeDefinition, $description, $tags, $customPriority, $parentIdTag],
        ];
    }

    /**
     * @test
     */
    public function actionsInfoProviderSupportsMultipleFiles()
    {
        $description1 = 'Some description for some-scope-1';
        $scopeDefinition1 = [
            'some-scope-1' => [
                'actions' => ['some-action' => $description1]
            ]
        ];

        $description2 = 'Some description for some-scope-2';
        $scopeDefinition2 = [
            'some-scope-2' => [
                'actions' => ['some-action' => $description2]
            ]
        ];

        $actionsInfoProvider = new ActionsInfoProvider([$scopeDefinition1, $scopeDefinition2]);
        $actionInfo1 = $actionsInfoProvider->getActionsInfo('some-scope-1');
        $actionInfo2 = $actionsInfoProvider->getActionsInfo('some-scope-2');


        $generatedDescription = $actionInfo1->getDescription('some-action', 'some-id', []);
        $this->assertSame($description1, $generatedDescription);

        $generatedDescription = $actionInfo2->getDescription('some-action', 'some-id', []);
        $this->assertSame($description2, $generatedDescription);
    }

    /**
     * @test
     */
    public function actionsInfoSupportsActionsFromMultipleFilesForOneScope()
    {
        $description1 = 'Some description for some-action-1 with %action-1-tag%';
        $scopeDefinition1 = [
            'some-scope' => [
                'tags' => ['action-1-tag' => '/'],
                'actions' => ['some-action-1' => $description1]
            ]
        ];

        $description2 = 'Some description for some-action-2 with %action-2-tag%';
        $scopeDefinition2 = [
            'some-scope' => [
                'tags' => ['action-2-tag' => '/'],
                'actions' => ['some-action-2' => $description2]
            ]
        ];

        $actionsInfoProvider = new ActionsInfoProvider([$scopeDefinition1, $scopeDefinition2]);
        $actionsInfo = $actionsInfoProvider->getActionsInfo('some-scope');

        $generatedDescription = $actionsInfo->getDescription('some-action-1', 'some-id', ['action-1-tag' => 'tag 1']);
        $this->assertSame(str_replace('%action-1-tag%', 'tag 1', $description1), $generatedDescription);

        $generatedDescription = $actionsInfo->getDescription('some-action-2', 'some-id', ['action-2-tag' => 'tag 2']);
        $this->assertSame(str_replace('%action-2-tag%', 'tag 2', $description2), $generatedDescription);
    }


    /**
     * @test
     */
    public function actionsInfoSupportsActionsFromMultipleFilesWithRedefinedTags()
    {
        $description1 = 'Some description for some-action-1 with %some-tag%';
        $scopeDefinition1 = [
            'some-scope' => [
                'tags' => ['some-tag' => '/'],
                'actions' => ['some-action-1' => $description1]
            ]
        ];

        $description2 = 'Some description for some-action-2 with %some-tag%';
        $scopeDefinition2 = [
            'some-scope' => [
                'tags' => ['some-tag' => '/'],
                'actions' => ['some-action-2' => $description2]
            ]
        ];

        $actionsInfoProvider = new ActionsInfoProvider([$scopeDefinition1, $scopeDefinition2]);
        $actionsInfo = $actionsInfoProvider->getActionsInfo('some-scope');

        $generatedDescription = $actionsInfo->getDescription('some-action-1', 'some-id', ['some-tag' => 'tag 1']);
        $this->assertSame(str_replace('%some-tag%', 'tag 1', $description1), $generatedDescription);

        $generatedDescription = $actionsInfo->getDescription('some-action-2', 'some-id', ['some-tag' => 'tag 2']);
        $this->assertSame(str_replace('%some-tag%', 'tag 2', $description2), $generatedDescription);
    }

    /**
     * @test
     */
    public function actionsInfoTakesIteratorAsParameter()
    {
        $description = 'Some description';
        $scopeDefinition = [
            'some-scope' => [
                'actions' => ['some-action' => $description]
            ]
        ];

        $actionsInfoProvider = new ActionsInfoProvider(new \ArrayIterator([$scopeDefinition]));
        $this->assertInstanceOf(ActionsInfo::class, $actionsInfoProvider->getActionsInfo('some-scope'));
    }
}
