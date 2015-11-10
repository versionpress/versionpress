<?php
namespace VersionPress\Storages;

use Nette\Utils\Strings;
use VersionPress\ChangeInfos\OptionChangeInfo;
use VersionPress\Database\EntityInfo;
use VersionPress\Utils\IniSerializer;

class OptionStorage extends DirectoryStorage {

    const PREFIX_PLACEHOLDER = "<<table-prefix>>";

    public static $optionsBlacklist = array(
        'cron',          // Cron, siteurl and home are specific for environment, so they're not saved, too.
        'home',
        'siteurl',
        'db_upgraded',
        'recently_edited',
        'auto_updater.lock',
        'can_compress_scripts',
        'auto_core_update_notified',
    );

    public static $regenerableOptions = array(
        '.*_children',
    );

    /** @var EntityInfo */
    private $entityInfo;
    /** @var string */
    private $tablePrefix;

    public function __construct($directory, $entityInfo, $tablePrefix) {
        parent::__construct($directory, $entityInfo);
        $this->entityInfo = $entityInfo;
        $this->tablePrefix = $tablePrefix;
    }

    public function save($data) {
        unset($data['option_id']);
        return parent::save($data);
    }

    protected function createChangeInfo($oldEntity, $newEntity, $action = null) {
        return new OptionChangeInfo($action, $newEntity['option_name']);
    }

    protected function serializeEntity($optionName, $entity) {
        $optionName = $this->maybeReplacePrefixWithPlaceholder($optionName);
        return parent::serializeEntity($optionName, $entity);
    }

    protected function deserializeEntity($serializedEntity) {
        $entity = IniSerializer::deserializeFlat($serializedEntity);
        $flatEntity = $this->flattenEntity($entity);
        if (isset($flatEntity[$this->entityInfo->idColumnName])) {
            $flatEntity[$this->entityInfo->idColumnName] = $this->maybeReplacePlaceholderWithPrefix($flatEntity[$this->entityInfo->idColumnName]);
        }
        return $flatEntity;
    }

    public function shouldBeSaved($data) {
        $id = $data[$this->entityInfo->idColumnName];
        return !($this->isTransientOption($id) || $this->isRegenerableOption($id) || in_array($id, self::$optionsBlacklist));
    }

    private function isTransientOption($id) {
        return substr($id, 0, 1) === '_'; // All transient options begin with underscore - there's no need to save them
    }

    private function isRegenerableOption($id) {
        foreach (self::$regenerableOptions as $pattern) {
            if (preg_match('/^' . $pattern . '$/', $id))
                return true;
        }
        return false;
    }

    private function maybeReplacePrefixWithPlaceholder($key) {
        if (Strings::startsWith($key, $this->tablePrefix)) {
            return self::PREFIX_PLACEHOLDER . Strings::substring($key, Strings::length($this->tablePrefix));
        }
        return $key;
    }

    private function maybeReplacePlaceholderWithPrefix($key) {
        if (Strings::startsWith($key, self::PREFIX_PLACEHOLDER)) {
            return $this->tablePrefix . Strings::substring($key, Strings::length(self::PREFIX_PLACEHOLDER));
        }
        return $key;
    }

    public function getEntityFilename($optionName, $parentId = null) {
        $sanitizedOptionName = urlencode($optionName);
        $sanitizedOptionName = str_replace('.', '%2E', $sanitizedOptionName);
        return parent::getEntityFilename($sanitizedOptionName, $parentId);
    }
}
