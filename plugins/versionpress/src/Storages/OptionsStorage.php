<?php

namespace VersionPress\Storages;

use Nette\Utils\Strings;
use VersionPress\ChangeInfos\OptionChangeInfo;
use VersionPress\Utils\IniSerializer;

class OptionsStorage extends SingleFileStorage {

    /** @var string */
    private $dbPrefix;
    const PREFIX_PLACEHOLDER = "<<table-prefix>>";

    protected $notSavedFields = array('option_id');

    function __construct($file, $entityInfo, $dbPrefix) {
        parent::__construct($file, $entityInfo);
        $this->dbPrefix = $dbPrefix;
    }

    public function shouldBeSaved($data) {
        $blacklist = array(
            'cron',          // Cron, siteurl and home are specific for environment, so they're not saved, too.
            'home',
            'siteurl',
            'db_upgraded',
            'recently_edited',
            'auto_updater.lock',
            'can_compress_scripts',
            'auto_core_update_notified',
        );

        $id = $data[$this->entityInfo->idColumnName];
        return !($this->isTransientOption($id) || in_array($id, $blacklist));
    }

    protected function createChangeInfo($oldEntity, $newEntity, $action = null) {
        return new OptionChangeInfo($action, $newEntity[$this->entityInfo->idColumnName]);
    }

    protected function loadEntities() {
        parent::loadEntities();

        $entitiesWithPlaceholders = $this->entities;
        $entities = array();

        foreach ($entitiesWithPlaceholders as $id => $entity) {
            $entities[$this->maybeReplacePlaceholderWithPrefix($id)] = $entity;
        }

        foreach ($entities as $id => &$entity) {
            $entity[$this->entityInfo->vpidColumnName] = $id;
        }

        $this->entities = $entities;
    }

    private function isTransientOption($id) {
        return substr($id, 0, 1) === '_'; // All transient options begin with underscore - there's no need to save them
    }

    protected function saveEntities() {
        $originalEntities = $this->entities;
        $entitiesWithPlaceholders = array();

        foreach ($originalEntities as $id => $entity) {
            $id = $this->maybeReplacePrefixWithPlaceholder($id);
            $entitiesWithPlaceholders[$id] = $entity;
            $entitiesWithPlaceholders[$id]['option_name'] = $id;
        }

        $this->entities = $entitiesWithPlaceholders;

        parent::saveEntities();
        $this->entities = $originalEntities;
    }

    private function maybeReplacePrefixWithPlaceholder($key) {
        if (Strings::startsWith($key, $this->dbPrefix)) {
            return self::PREFIX_PLACEHOLDER . Strings::substring($key, Strings::length($this->dbPrefix));
        }
        return $key;
    }

    private function maybeReplacePlaceholderWithPrefix($key) {
        if (Strings::startsWith($key, self::PREFIX_PLACEHOLDER)) {
            return $this->dbPrefix . Strings::substring($key, Strings::length(self::PREFIX_PLACEHOLDER));
        }
        return $key;
    }
}