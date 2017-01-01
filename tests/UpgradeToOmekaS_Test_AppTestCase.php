<?php
/**
 * @copyright Daniel Berthereau, 2017
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 * @package UpgradeToOmekaS
 */

/**
 * Base class for UpgradeToOmekaS tests.
 *
 * @todo True unit cases.
 */
class UpgradeToOmekaS_Test_AppTestCase extends Omeka_Test_AppTestCase
{
    const PLUGIN_NAME = 'UpgradeToOmekaS';

    protected $_tmpdir;
    protected $_zippath;
    protected $_baseDir;
    protected $_isBaseDirCreated = false;

    public function setUp()
    {
        parent::setUp();

        $pluginHelper = new Omeka_Test_Helper_Plugin;
        $pluginHelper->setUp(self::PLUGIN_NAME);

        // Omeka S requires Apache.
        $_SERVER['SERVER_SOFTWARE'] = 'Apache 2.4';
        // Set Omeka dir the base dir of the server.
        // $_SERVER['DOCUMENT_ROOT'] = sys_get_temp_dir();

        $this->_tmpdir = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR . 'UpgradeToOmekaS_unit_test';

        //This is where the install test will be done by default.
        $this->_baseDir = $this->_tmpdir
            . DIRECTORY_SEPARATOR . 'Semantic';
        $test = file_exists($this->_baseDir)
            ? __('The test base dir %s must not exist.', $this->_baseDir)
            : __('You should remove it.');
        $this->assertEquals($test, __('You should remove it.'));

        // This is where the downloaded package omeka-s.zip is saved.
        // TODO Move it in the main setup of the tests.
        $this->_zippath = $this->_tmpdir . DIRECTORY_SEPARATOR . 'omeka-s.zip';

        // To clear cache after a crash.
        $this->_removeStubPlugin();

        // The prechecks fail when there are some files in "files/original".
        // So a precheck is done to get the total default of answers.
        $path = FILES_DIR . DIRECTORY_SEPARATOR . 'original';
        $totalFiles = UpgradeToOmekaS_Common::countFilesInDir($path);
    }

    public function tearDown()
    {
        $this->_removeStubPlugin();
        $this->_removeBaseDir();
        $this->_removeEmptyDownloadedFile();
        $this->_removeTableOmekaS();
        $this->_removeRecords('Process');
        $this->_removeRecords('User');
        $this->_removeRecords('Item');
        $this->_removeRecords('Collection');

        parent::tearDown();
    }

    protected function _createStubPlugin()
    {
        $path = PLUGIN_DIR . DIRECTORY_SEPARATOR . 'Stub' . DIRECTORY_SEPARATOR . 'plugin.ini';
        $this->assertFalse(file_exists($path));

        $result = mkdir(dirname($path));
        $this->assertTrue($result);
        $content = <<<PLUGIN
[info]
name = "Stub"
author = "Daniel Berthereau"
description = "Stub description"
license = "CeCILL v2.1"
link = "https://github.com/Daniel-KM/UpgradeToOmekaS"
support_link = "https://github.com/Daniel-KM/UpgradeToOmekaS/issues"
optional_plugins = ""
version = "2.2"
omeka_minimum_version = "2.2.2"
omeka_target_version = "2.5"
tags = "archive, upgrade"
PLUGIN;
        $result = file_put_contents($path, $content);
        $this->assertTrue((boolean) $result);
    }

    protected function _removeStubPlugin()
    {
        $path = PLUGIN_DIR . DIRECTORY_SEPARATOR . 'Stub' . DIRECTORY_SEPARATOR . 'plugin.ini';
        $path = dirname($path);
        if (file_exists($path)) {
            UpgradeToOmekaS_Common::removeDir($path, true);
        }
    }

    protected function _createBaseDir()
    {
        $path = $this->_baseDir;
        $this->assertFalse(file_exists($path));
        $result = mkdir($path);
        $this->assertTrue($result);
        $this->_isBaseDirCreated = $this->_baseDir;
    }

    protected function _removeBaseDir()
    {
        $path = rtrim($this->_baseDir, '/ ');
        // An important internal check.
        if (empty($this->_isBaseDirCreated)
                || $path == BASE_DIR
                || $path == dirname(BASE_DIR)
            ) {
            return;
        }
        if (file_exists($path)) {
            chmod($path, 0755);
            UpgradeToOmekaS_Common::removeDir($path, true);
        }
    }

    protected function _removeEmptyDownloadedFile()
    {
        $path = $this->_zippath;
        if (file_exists($path) && filesize($path) === 0) {
            unlink($path);
        }
    }

    protected function _removeTableOmekaS()
    {
        $processor = new UpgradeToOmekaS_Processor_Base();
        // $target = $processor->getTarget();
        // $result = $target->removeTables();
        $omekasTables = $processor->getMerged('_tables_omekas');
        $sql = 'SET foreign_key_checks = 0;';
        $result = get_db()->query($sql);
        $sql = 'DROP TABLE IF EXISTS `' . implode('`, `', $omekasTables) . '`;';
        $result = get_db()->query($sql);
        $sql = 'SET foreign_key_checks = 1;';
        $result = get_db()->query($sql);
    }

    protected function _removeRecords($recordType)
    {
        $records = get_records($recordType, array(), 0);
        foreach ($records as $record) {
            $record->delete();
        }
    }

    protected function _prepareProcessor(
        $processorName,
        $params = null,
        $methods = array(),
        $checkDir = true,
        $isProcessing = true
    ) {
        set_option('upgrade_to_omeka_s_process_status', Process::STATUS_IN_PROGRESS);
        $defaultParams = array(
            'database' => array(
                'type' => 'share',
                'prefix' => 'omekas_',
            ),
            'base_dir' => $this->_baseDir,
            'files_type' => 'copy',
        );
        if (is_null($params)) {
            $params = $defaultParams;
        }
        // Add and replace values.
        else {
            $params = array_merge($defaultParams, $params);
        }

        $processor = new UpgradeToOmekaS_Processor_Base();
        $processors = $processor->getProcessors();
        $this->assertTrue(isset($processors[$processorName]));

        if ($checkDir) {
            $baseDir = $processor->getParam('base_dir');
            $result = !file_exists($baseDir) || UpgradeToOmekaS_Common::isDirEmpty($baseDir);
            $this->assertTrue($result);
            $this->_isBaseDirCreated = true;
        }

        set_option('upgrade_to_omeka_s_process_status',
            $isProcessing ? Process::STATUS_IN_PROGRESS : '');

        if ($methods) {
            foreach ($methods as $method) {
                foreach ($processors as $name => $processor) {
                    if (in_array($method, $processor->processMethods)) {
                        $defaultmethods = $processor->processMethods;
                        $processor->processMethods = array($method);
                        $processor->setParams($params);
                        $processor->process();
                        $processor->processMethods = $defaultmethods;
                    }
                }
            }
        }
        // In the case there is no method.
        else {
            $processor = $processors[$processorName];
            $processor->setParams($params);
            if ($checkDir) {
                $baseDir = $processor->getParam('base_dir');
                $result = !file_exists($baseDir) || UpgradeToOmekaS_Common::isDirEmpty($baseDir);
                $this->assertTrue($result);
                $this->_isBaseDirCreated = true;
            }
        }

        return $processors[$processorName];
    }

    protected function _checkDownloadedOmekaS()
    {
        $path = $this->_zippath;
        if (!file_exists($path)) {
            $this->markTestSkipped(__('The test requires that the file "omeka-s.zip" is saved in temp folder.'));
        }
        // Check correct file.
        else {
            $processor = new UpgradeToOmekaS_Processor_CoreServer();
            if (filesize($path) != $processor->module['size']
                   || md5_file($path) != $processor->module['md5']
                ) {
                $this->markTestSkipped(__('A file "%s" exists and this is not a test one.', $path));
            }
        }
    }
}
