<?php
namespace VersionPress\Synchronizers;

use VersionPress\Database\Database;
use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\ShortcodesReplacer;
use VersionPress\Storages\OptionStorage;
use VersionPress\Storages\Storage;
use VersionPress\Utils\AbsoluteUrlReplacer;
use VersionPress\Utils\ArrayUtils;
use VersionPress\Utils\QueryLanguageUtils;
use VersionPress\Utils\ReferenceUtils;
use wpdb;

/**
 * Options synchronizer. Skips transient options and a couple of hardcoded values like
 * `cron` or `siteurl`, see the `synchronize()` method.
 */
class OptionsSynchronizer implements Synchronizer {

    /** @var OptionStorage */
    private $optionStorage;

    /** @var Database */
    private $database;
    /** @var AbsoluteUrlReplacer */
    private $urlReplacer;
    /** @var ShortcodesReplacer */
    private $shortcodesReplacer;

    private $tableName;
    private $options;

    /** @var DbSchemaInfo */
    private $dbSchema;

    function __construct(Storage $optionStorage, $database, DbSchemaInfo $dbSchema, AbsoluteUrlReplacer $urlReplacer, ShortcodesReplacer $shortcodesReplacer) {
        $this->optionStorage = $optionStorage;
        $this->database = $database;
        $this->urlReplacer = $urlReplacer;
        $this->tableName = $dbSchema->getPrefixedTableName('option');
        $this->dbSchema = $dbSchema;
        $this->shortcodesReplacer = $shortcodesReplacer;
    }

    function synchronize($task, $entitiesToSynchronize = null) {
        $this->maybeInit($entitiesToSynchronize);
        $options = $this->options;

        if (count($options) > 0) {
            $syncQuery = "INSERT INTO {$this->tableName} (option_name, option_value, autoload) VALUES ";
            foreach ($options as $optionName => $option) {
                $option = $this->shortcodesReplacer->restoreShortcodesInEntity('option', $option);

                $option = $this->urlReplacer->restore($option);
                $option = $this->maybeRestoreReference($option);
                if (!isset($option['autoload'])) $option['autoload'] = 'yes'; // default value
                if (!isset($option['option_value'])) $option['option_value'] = '';
                $syncQuery .= "(\"$optionName\", \"" . $this->database->realEscape($option['option_value']) . "\", \"$option[autoload]\"),";
            }

            $syncQuery[strlen($syncQuery) - 1] = " "; // strip last comma
            $syncQuery .= " ON DUPLICATE KEY UPDATE option_value = VALUES(option_value), autoload = VALUES(autoload);";

            $this->database->query($syncQuery);
        }

        $entityInfo = $this->dbSchema->getEntityInfo('option');
        $rules = $entityInfo->getRulesForIgnoredEntities();
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
        return array();
    }

    private function maybeInit($optionsToSynchronize) {
        if ($this->options === null) {
            $this->options = $this->loadOptionsFromStorage($optionsToSynchronize);
        }
    }

    private function loadOptionsFromStorage($optionsToSynchronize) {
        if ($optionsToSynchronize === null) {
            return $this->optionStorage->loadAll();
        }

        $options = array();
        foreach ($optionsToSynchronize as $optionToSynchronize) {
            $optionName = $optionToSynchronize['vp_id'];
            if ($this->optionStorage->exists($optionName)) {
                $option = $this->optionStorage->loadEntity($optionName);
                $options[$option['option_name']] = $option;
            }
        }

        return $options;
    }

    private function maybeRestoreReference($option) {
        $entityInfo = $this->dbSchema->getEntityInfo('option');
        foreach ($entityInfo->valueReferences as $reference => $targetEntity) {
            $referenceDetails = ReferenceUtils::getValueReferenceDetails($reference);
            if ($option[$referenceDetails['source-column']] === $referenceDetails['source-value'] && isset($option[$referenceDetails['value-column']])) {
                $vpid = $option[$referenceDetails['value-column']];
                $vpidTable = $this->dbSchema->getPrefixedTableName('vp_id');
                $targetTable = $this->dbSchema->getTableName($targetEntity);
                $dbId = $this->database->getVariable("SELECT id FROM $vpidTable WHERE `table`='$targetTable' AND vp_id=UNHEX('$vpid')");
                $option[$referenceDetails['value-column']] = $dbId;
            }
        }

        return $option;
    }
}
