<?php

namespace VersionPress\Git\ChangeInfoPreprocessors;

use VersionPress\ChangeInfos\ChangeInfo;
use VersionPress\ChangeInfos\ChangeInfoFactory;
use VersionPress\ChangeInfos\TrackedChangeInfo;

class TermTaxonomyPreprocessor implements ChangeInfoPreprocessor
{

    /** @var ChangeInfoFactory */
    private $changeInfoFactory;

    public function __construct(ChangeInfoFactory $changeInfoFactory)
    {
        $this->changeInfoFactory = $changeInfoFactory;
    }

    /**
     * Reads taxonomy from `term_taxonomy` action and assings it to `term`.
     *
     * @param ChangeInfo[] $changeInfoList
     * @return ChangeInfo[][]
     */
    public function process($changeInfoList)
    {
        /** @var TrackedChangeInfo[] $termChangeInfos */
        $termChangeInfos = array_filter($changeInfoList, function ($changeInfo) {
            return $changeInfo instanceof TrackedChangeInfo && $changeInfo->getScope() === 'term';
        });

        if (!$termChangeInfos) {
            return [$changeInfoList];
        }

        /** @var TrackedChangeInfo[] $termTaxonomyChangeInfos */
        $termTaxonomyChangeInfos = array_filter($changeInfoList, function ($changeInfo) {
            return $changeInfo instanceof TrackedChangeInfo && $changeInfo->getScope() === 'term_taxonomy';
        });

        $taxonomies = array_combine(
            array_map(function (TrackedChangeInfo $changeInfo) {
                return $changeInfo->getCustomTags()['VP-Term-Id'];
            }, $termTaxonomyChangeInfos),
            array_map(function (TrackedChangeInfo $changeInfo) {
                return $changeInfo->getCustomTags()['VP-TermTaxonomy-Taxonomy'];
            }, $termTaxonomyChangeInfos)
        );

        foreach ($termChangeInfos as $i => $termChangeInfo) {
            $termId = $termChangeInfo->getId();
            if (isset($taxonomies[$termId])) {
                $tags = $termChangeInfo->getCustomTags();
                $tags['VP-Term-Taxonomy'] = $taxonomies[$termId];

                $changeInfoList[$i] = $this->changeInfoFactory->createTrackedChangeInfo(
                    $termChangeInfo->getScope(),
                    $termChangeInfo->getAction(),
                    $termChangeInfo->getId(),
                    $tags,
                    $termChangeInfo->getChangedFiles()
                );
            }
        }

        return [$changeInfoList];
    }
}
