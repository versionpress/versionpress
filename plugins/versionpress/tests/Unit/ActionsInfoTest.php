<?php

namespace VersionPress\Tests\Unit;

use org\bovigo\vfs\vfsStream;
use Symfony\Component\Yaml\Yaml;
use VersionPress\Git\ActionsInfo;
use VersionPress\Tests\Utils\HookMock;

class ActionsInfoTest extends \PHPUnit_Framework_TestCase
{
    private $root;

    protected function setUp()
    {
        $this->root = vfsStream::setup();
        HookMock::setUp();
    }

    /**
     * @test
     */
    public function actionsInfoCreatesDescriptionFromActionFile()
    {
        $description = 'Some description';
        $scopeDefinition = [
            'some-scope' => [
                'actions' => ['some-action' => $description]
            ]
        ];

        $actionsFilePath = $this->createActionsFile($scopeDefinition);

        $actionsInfo = new ActionsInfo([$actionsFilePath]);
        $generatedDescription = $actionsInfo->getDescription('some-scope', 'some-action', 'some-id', []);
        $this->assertSame($description, $generatedDescription);
    }

    /**
     * @test
     */
    public function actionsInfoReplacesTagPlaceholderWithItsValue()
    {
        $description = 'Some description containing %VP-Tag%';
        $scopeDefinition = [
            'some-scope' => [
                'tags' => ['VP-Tag' => '/'],
                'actions' => ['some-action' => $description]
            ]
        ];

        $actionsFilePath = $this->createActionsFile($scopeDefinition);

        $actionsInfo = new ActionsInfo([$actionsFilePath]);
        $generatedDescription = $actionsInfo->getDescription('some-scope', 'some-action', 'some-id', ['VP-Tag' => 'tag value']);
        $this->assertSame('Some description containing tag value', $generatedDescription);
    }

    /**
     * @test
     */
    public function actionsInfoReplacesVpidPlaceholderWithVpid()
    {
        $description = 'Some description containing %VPID%';
        $scopeDefinition = [
            'some-scope' => [
                'actions' => ['some-action' => $description]
            ]
        ];

        $actionsFilePath = $this->createActionsFile($scopeDefinition);

        $actionsInfo = new ActionsInfo([$actionsFilePath]);
        $generatedDescription = $actionsInfo->getDescription('some-scope', 'some-action', 'some-id', []);
        $this->assertSame('Some description containing some-id', $generatedDescription);
    }

    /**
     * @test
     */
    public function actionsInfoSupportsMultipleFiles()
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

        $actionsFilePath1 = $this->createActionsFile($scopeDefinition1);
        $actionsFilePath2 = $this->createActionsFile($scopeDefinition2);

        $actionsInfo = new ActionsInfo([$actionsFilePath1, $actionsFilePath2]);

        $generatedDescription = $actionsInfo->getDescription('some-scope-1', 'some-action', 'some-id', []);
        $this->assertSame($description1, $generatedDescription);

        $generatedDescription = $actionsInfo->getDescription('some-scope-2', 'some-action', 'some-id', []);
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

        $actionsFilePath1 = $this->createActionsFile($scopeDefinition1);
        $actionsFilePath2 = $this->createActionsFile($scopeDefinition2);

        $actionsInfo = new ActionsInfo([$actionsFilePath1, $actionsFilePath2]);

        $generatedDescription = $actionsInfo->getDescription('some-scope', 'some-action-1', 'some-id', ['action-1-tag' => 'tag 1']);
        $this->assertSame(str_replace('%action-1-tag%', 'tag 1', $description1), $generatedDescription);

        $generatedDescription = $actionsInfo->getDescription('some-scope', 'some-action-2', 'some-id', ['action-2-tag' => 'tag 2']);
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

        $actionsFilePath1 = $this->createActionsFile($scopeDefinition1);
        $actionsFilePath2 = $this->createActionsFile($scopeDefinition2);

        $actionsInfo = new ActionsInfo([$actionsFilePath1, $actionsFilePath2]);

        $generatedDescription = $actionsInfo->getDescription('some-scope', 'some-action-1', 'some-id', ['some-tag' => 'tag 1']);
        $this->assertSame(str_replace('%some-tag%', 'tag 1', $description1), $generatedDescription);

        $generatedDescription = $actionsInfo->getDescription('some-scope', 'some-action-2', 'some-id', ['some-tag' => 'tag 2']);
        $this->assertSame(str_replace('%some-tag%', 'tag 2', $description2), $generatedDescription);
    }

    /**
     * Creates a virtual file containg YAML created from $scopesDefinition and returns its path.
     *
     * @param array $scopesDefinition
     * @return string
     */
    private function createActionsFile($scopesDefinition)
    {
        static $fileNumber = 0;

        $fileName = 'actions-' . ($fileNumber++) . '.yml';
        $actionFile = vfsStream::newFile($fileName)->at($this->root);

        file_put_contents($actionFile->url(), Yaml::dump($scopesDefinition));
        return $actionFile->url();
    }
}
