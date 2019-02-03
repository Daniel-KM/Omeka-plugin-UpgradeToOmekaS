<?php

/**
 * Upgrade ZoteroImport to Omeka S.
 *
 * @package UpgradeToOmekaS
 */
class UpgradeToOmekaS_Processor_ZoteroImport extends UpgradeToOmekaS_Processor_Abstract
{

    public $pluginName = 'ZoteroImport';
    public $minVersion = '2.0';
    public $maxVersion = '';

    public $module = array(
        'name' => 'ZoteroImport',
        'version' => '1.0.0',
        'url' => 'https://github.com/omeka-s-modules/ZoteroImport/releases/download/v%s/ZoteroImport-%s.zip',
        'size' => 39118,
        'sha1' => '4ba5d1f0d444296bb42b60a1e8798c2e83f26dbe',
        'type' => 'port',
        'note' => '',
        'original_ids' => true,
        'install' => array(
            // Copied from the original Module.php.
            'sql' => '
SET FOREIGN_KEY_CHECKS=0;
CREATE TABLE zotero_import (id INT AUTO_INCREMENT NOT NULL, job_id INT DEFAULT NULL, undo_job_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, version INT NOT NULL, UNIQUE INDEX UNIQ_82A3EEB8BE04EA9 (job_id), UNIQUE INDEX UNIQ_82A3EEB84C276F75 (undo_job_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
CREATE TABLE zotero_import_item (id INT AUTO_INCREMENT NOT NULL, import_id INT NOT NULL, item_id INT NOT NULL, zotero_key VARCHAR(255) NOT NULL, INDEX IDX_86A2392BB6A263D9 (import_id), INDEX IDX_86A2392B126F525E (item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
ALTER TABLE zotero_import ADD CONSTRAINT FK_82A3EEB8BE04EA9 FOREIGN KEY (job_id) REFERENCES job (id) ON DELETE CASCADE;
ALTER TABLE zotero_import ADD CONSTRAINT FK_82A3EEB84C276F75 FOREIGN KEY (undo_job_id) REFERENCES job (id) ON DELETE CASCADE;
ALTER TABLE zotero_import_item ADD CONSTRAINT FK_86A2392BB6A263D9 FOREIGN KEY (import_id) REFERENCES zotero_import (id) ON DELETE CASCADE;
ALTER TABLE zotero_import_item ADD CONSTRAINT FK_86A2392B126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE;
SET FOREIGN_KEY_CHECKS=1;
',
        ),
    );

    public $tables = array(
        'zotero_import',
        'zotero_import_item',
    );

    public $processMethods = array(
        '_installModule',
    );

    public $mapping_models = array(
        'zotero_imports' => 'zotero_import',
        'zotero_import_items' => 'zotero_import_item',
    );

    protected function _upgradeData()
    {
        $this->_upgradeDataZoteroImport();
        $this->_upgradeDataZoteroImportItem();
    }

    protected function _upgradeDataZoteroImport()
    {
        $recordType = 'ZoteroImportImport';

        $totalRecords = total_records($recordType);
        if (empty($totalRecords)) {
            $this->_log('[' . __FUNCTION__ . ']: ' . __('No Zotero import to upgrade.'),
                Zend_Log::INFO);
            return;
        }
        $this->_progress(0, $totalRecords);

        $db = $this->_db;
        $target = $this->getTarget();
        // $targetDb = $target->getDb();

        // The process uses the regular queries of Omeka in order to keep
        // only good records and to manage filters.
        $table = $db->getTable($recordType);

        $mappedCollectionIds = $this->fetchMappedIds('Collection');
        // $user = $this->getParam('user');

        $loops = floor(($totalRecords - 1) / $this->maxChunk) + 1;
        for ($page = 1; $page <= $loops; $page++) {
            $this->_progress(($page - 1) * $this->maxChunk);
            $records = $table->findBy(array(), $this->maxChunk, $page);

            $toInserts = array();
            foreach ($records as $record) {
                $hasCollectionId = isset($mappedCollectionIds[$record->collection_id]);
                $itemSetId = $hasCollectionId
                    ? $mappedCollectionIds[$record->collection_id]
                    : null;

                $process = get_record_by_id('Process', $record->process_id);
                if ($process) {
                    $jobArguments = $process->getArguments();
                    if ($hasCollectionId && $itemSetId) {
                        $jobArguments['itemSet'] = $itemSetId;
                        unset($jobArguments['collectionId']);
                    } else {
                        $jobArguments['itemSet'] = null;
                    }
                    $jobArguments['type'] = isset($jobArguments['libraryType']) ? $jobArguments['libraryType'] : null;
                    unset($jobArguments['libraryType']);
                    $jobArguments['id'] = isset($jobArguments['libraryId']) ? $jobArguments['libraryId'] : null;
                    unset($jobArguments['libraryId']);
                    $jobArguments['collectionKey'] = isset($jobArguments['libraryCollectionId']) ? $jobArguments['libraryCollectionId'] : null;
                    unset($jobArguments['libraryCollectionId']);
                    $jobArguments['apiKey'] = isset($jobArguments['privateKey']) ? $jobArguments['privateKey'] : null;
                    unset($jobArguments['privateKey']);
                    $jobArguments['importFiles'] = null;
                    $jobArguments['version'] = 0;
                    $jobArguments['timestamp'] = null;
                    unset($jobArguments['zoteroImportId']);

                    $toInsert = array();
                    $toInsert['id'] = $process->id;
                    $toInsert['owner_id'] = $process->user_id;
                    $toInsert['pid'] = $process->pid;
                    $toInsert['status'] = $process->status;
                    $toInsert['class'] = $process->class;
                    $toInsert['args'] = $target->toJson($jobArguments);
                    $toInsert['log'] = '';
                    $toInsert['started'] = $process->started;
                    $toInsert['ended'] = $process->stopped;
                    $toInserts['job'][] = $target->cleanQuote($toInsert);
                } else {
                    $jobArguments = array(
                        'itemSet' => $itemSetId,
                        'type' => null,
                        'id' => null,
                        'collectionKey' => null,
                        'apiKey' => null,
                        'importFiles' => null,
                        'version' => 0,
                        'timestamp' => null,
                    );

                    $toInsert = array();
                    $toInsert['id'] = $record->process_id;
                    $toInsert['owner_id'] = null;
                    $toInsert['pid'] = null;
                    // The job is set to completed, but it's unknown.
                    $toInsert['status'] = 'completed';
                    $toInsert['class'] = 'ZoteroImport_ImportProcess';
                    $toInsert['args'] = $target->toJson($jobArguments);
                    $toInsert['log'] = '';
                    $toInsert['started'] = $this->_datetime;
                    $toInsert['ended'] = null;
                    $toInserts['job'][] = $target->cleanQuote($toInsert);
                }

                $toInsert = array();
                $toInsert['id'] = $record->id;
                $toInsert['job_id'] = $record->process_id;
                $toInsert['undo_job_id'] = null;
                // TODO Get the true name of the Zotero user from the collection? If not exists?
                $toInsert['name'] = 'UpgradedOmekaZoteroUser';
                $toInsert['url'] = '';
                $toInsert['version'] = 0;
                $toInserts['zotero_import'][] = $target->cleanQuote($toInsert);
            }

            $target->insertRowsInTables($toInserts);
        }

        $this->_log('[' . __FUNCTION__ . ']: ' . __('All Zotero imports (%d) have been upgraded.',
            $totalRecords), Zend_Log::INFO);
    }

    protected function _upgradeDataZoteroImportItem()
    {
        $recordType = 'ZoteroImportItem';

        $totalRecords = total_records($recordType);
        if (empty($totalRecords)) {
            $this->_log('[' . __FUNCTION__ . ']: ' . __('No Zotero import item to upgrade.'),
                Zend_Log::INFO);
            return;
        }
        $this->_progress(0, $totalRecords);

        $db = $this->_db;
        $target = $this->getTarget();
        // $targetDb = $target->getDb();

        // The process uses the regular queries of Omeka in order to keep
        // only good records and to manage filters.
        $table = $db->getTable($recordType);

        $totalSkipped = 0;

        $loops = floor(($totalRecords - 1) / $this->maxChunk) + 1;
        for ($page = 1; $page <= $loops; $page++) {
            $this->_progress(($page - 1) * $this->maxChunk);
            $records = $table->findBy(array(), $this->maxChunk, $page);

            $toInserts = array();
            foreach ($records as $record) {
                if (empty($record->item_id)) {
                    ++$totalSkipped;
                    continue;
                }

                $toInsert = array();
                $toInsert['id'] = $record->id;
                $toInsert['import_id'] = $record->import_id;
                $toInsert['item_id'] = $record->item_id;
                $toInsert['zotero_key'] = $record->zotero_item_key;
                $toInserts['zotero_import_item'][] = $target->cleanQuote($toInsert);
            }

            $target->insertRowsInTables($toInserts);
        }

        if ($totalSkipped) {
            $this->_log('[' . __FUNCTION__ . ']: ' . __('Some items (%d/%d) where removed after importing. They were skipped In Omeka S.',
                $totalSkipped, $totalRecords), Zend_Log::INFO);
        } else {
            $this->_log('[' . __FUNCTION__ . ']: ' . __('All Zotero import items (%d) have been upgraded. Some data were skipped (item parent key, item type, item updated).',
                $totalRecords), Zend_Log::INFO);
        }
    }
}
