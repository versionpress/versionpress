<?php
namespace VersionPress\Synchronizers;

use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\EntityInfo;
use VersionPress\Database\ShortcodesReplacer;
use VersionPress\Database\VpidRepository;
use VersionPress\Storages\DirectoryStorage;
use VersionPress\Storages\Storage;
use VersionPress\Utils\AbsoluteUrlReplacer;
use VersionPress\Utils\ArrayUtils;
use VersionPress\Utils\QueryLanguageUtils;
use wpdb;

/**
 * Options synchronizer. Skips transient options and a couple of hardcoded values like
 * `cron` or `siteurl`, see the `synchronize()` method.
 */
class OptionsSynchronizer implements Synchronizer
{

    /** @var DirectoryStorage */
    private $optionStorage;

    /** @var Database */
    private $database;
    /** @var AbsoluteUrlReplacer */
    private $urlReplacer;
    /** @var ShortcodesReplacer */
    private $shortcodesReplacer;

    private $tableName;
    private $options;

    /** @var EntityInfo */
    private $entityInfo;
    /** @var VpidRepository */
    private $vpidRepository;

    public function __construct(
        Storage $storage,
        Database $database,
        EntityInfo $entityInfo,
        DbSchemaInfo $dbSchemaInfo,
        VpidRepository $vpidRepository,
        AbsoluteUrlReplacer $urlReplacer,
        ShortcodesReplacer $shortcodesReplacer
    ) {
        $this->optionStorage = $storage;
        $this->database = $database;
        $this->urlReplacer = $urlReplacer;
        $this->tableName = $database->prefix . $entityInfo->tableName;
        $this->entityInfo = $entityInfo;
        $this->shortcodesReplacer = $shortcodesReplacer;
        $this->vpidRepository = $vpidRepository;
    }

    public function synchronize($task, $entitiesToSynchronize = null)
    {
        if (is_array($entitiesToSynchronize) && count($entitiesToSynchronize) === 0) {
            return [];
        }

        $this->maybeInit($entitiesToSynchronize);
        $options = $this->options;

        if (count($options) > 0) {
            $syncQuery = "INSERT INTO {$this->tableName} (option_name, option_value, autoload) VALUES ";
            foreach ($options as $optionName => $option) {
                $option = $this->shortcodesReplacer->restoreShortcodesInEntity('option', $option);

                $option = $this->urlReplacer->restore($option);
                $option = $this->maybeRestoreReference($option);
                if (!isset($option['autoload'])) {
                    $option['autoload'] = 'yes';
                } // default value
                if (!isset($option['option_value'])) {
                    $option['option_value'] = '';
                }
                $syncQuery .= "(\"$optionName\", \"" . $this->database->_real_escape($option['option_value']) .
                    "\", \"$option[autoload]\"),";
            }

            $syncQuery[strlen($syncQuery) - 1] = " "; // strip last comma
            $syncQuery .= " ON DUPLICATE KEY UPDATE option_value = VALUES(option_value), autoload = VALUES(autoload);";

            $this->database->query($syncQuery);
        }

        $rules = $this->entityInfo->getRulesForIgnoredEntities();
        $restriction = join(' OR ', array_map(function ($rule) {
            $restrictionPart = QueryLanguageUtils::createSqlRestrictionFromRule($rule);
            return "($restrictionPart)";
        }, $rules));

        $deleteSql = "DELETE FROM {$this->tableName} WHERE NOT ($restriction)";

        if (count($options) > 0) {
            $updatedOptionNames = ArrayUtils::column($options, 'option_name');
            $restrictionForUpdatedOptions = "\"" . join('", "', $updatedOptionNames) . "\"";

            $deleteSql .= " AND option_name NOT IN ($restrictionForUpdatedOptions)";
        }

        if ($entitiesToSynchronize) {
            $synchronizedOptions = ArrayUtils::column($entitiesToSynchronize, 'vp_id');
            $restrictionForBasicSet = "\"" . join('", "', $synchronizedOptions) . "\"";
            $deleteSql .= " AND option_name IN ($restrictionForBasicSet)";
        }

        $this->database->query($deleteSql);
        return [];
    }

    private function maybeInit($optionsToSynchronize)
    {
        if ($this->options === null) {
            $this->options = $this->loadOptionsFromStorage($optionsToSynchronize);
        }
    }

    private function loadOptionsFromStorage($optionsToSynchronize)
    {
        if ($optionsToSynchronize === null) {
            return $this->optionStorage->loadAll();
        }

        $options = [];
        foreach ($optionsToSynchronize as $optionToSynchronize) {
            $optionName = $optionToSynchronize['vp_id'];
            if ($this->optionStorage->exists($optionName)) {
                $option = $this->optionStorage->loadEntity($optionName);
                $options[$option['option_name']] = $option;
            }
        }

        return $options;
    }

    private function maybeRestoreReference($option)
    {
        return $this->vpidRepository->restoreForeignKeys('option', $option);
    }
}
