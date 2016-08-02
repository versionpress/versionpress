<?php
namespace VersionPress\Utils;

use VersionPress\ChangeInfos\ChangeInfoFactory;
use VersionPress\DI\VersionPressServices;
use VersionPress\Git\GitRepository;

/**
 * Small helper class
 */
class UninstallationUtil
{

    /**
     * Returns true if VP uninstallation should remove the Git repo from the root folder (and back it
     * up somewhere). This is true if the first commit is VersionPress commit. Otherwise, the Git repo
     * was probably created by the user before VP was installed and we should keep the repo untouched.
     *
     * @return bool
     */
    public static function uninstallationShouldRemoveGitRepo()
    {
        global $versionPressContainer;
        /** @var GitRepository $repository */
        $repository = $versionPressContainer->resolve(VersionPressServices::REPOSITORY);
        $initialCommit = $repository->getInitialCommit();

        if (!$initialCommit) {
            return false;
        }

        /** @var ChangeInfoFactory $changeInfoFactory */
        $changeInfoFactory = $versionPressContainer->resolve(VersionPressServices::CHANGEINFO_FACTORY);

        $changeInfoEnvelope = $changeInfoFactory->buildChangeInfoEnvelopeFromCommitMessage($initialCommit->getMessage());

        $changeInfoList = $changeInfoEnvelope->getChangeInfoList();
        $firstChangeInfo = $changeInfoList[0];

        return $changeInfoList[0]->getScope() === 'versionpress' && $firstChangeInfo->getAction() === 'activate';
    }
}
