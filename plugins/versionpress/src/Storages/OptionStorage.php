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
        'rewrite_rules',
        'recently_edited',
        'auto_updater.lock',
        'can_compress_scripts',
        'auto_core_update_notified',
        'auth_key',
        'auth_salt',
        'logged_in_key',
        'logged_in_salt',

    );

    /** @var string */
    private $tablePrefix;
    /** @var string[] */
    private $taxonomies;

    public function __construct($directory, $entityInfo, $tablePrefix, $taxonomies) {
        parent::__construct($directory, $entityInfo);
        $this->tablePrefix = $tablePrefix;
        $this->taxonomies = $taxonomies;
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
        $entity = parent::deserializeEntity($serializedEntity);

        if (isset($entity[$this->entityInfo->idColumnName])) {
            $entity[$this->entityInfo->idColumnName] = $this->maybeReplacePlaceholderWithPrefix($entity[$this->entityInfo->idColumnName]);
        }
        return $entity;
    }

    public function shouldBeSaved($data) {
        $id = $data[$this->entityInfo->idColumnName];
        return parent::shouldBeSaved($data) && !($this->isTransientOption($id) || $this->isTaxonomyChildren($id) || in_array($id, self::$optionsBlacklist));
    }

    private function isTransientOption($id) {
        return substr($id, 0, 1) === '_'; // All transient options begin with underscore - there's no need to save them
    }

    private function isTaxonomyChildren($id) {
        $childrenSuffix = '_children';
        if (!Strings::endsWith($id, $childrenSuffix)) {
            return false;
        }

        $maybeTaxonomyName = Strings::substring($id, 0, Strings::length($id) - Strings::length($childrenSuffix));
        return in_array($maybeTaxonomyName, $this->taxonomies);
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
