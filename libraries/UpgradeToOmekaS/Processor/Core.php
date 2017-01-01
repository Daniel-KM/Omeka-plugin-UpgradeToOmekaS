<?php

/**
 * Upgrade Core to Omeka S.
 *
 * @internal All checks can be bypassed with another "Core" processor.
 *
 * @package UpgradeToOmekaS
 */
class UpgradeToOmekaS_Processor_Core extends UpgradeToOmekaS_Processor_Abstract
{
    public $pluginName = 'Core';
    public $minVersion = '2.3.1';
    public $maxVersion = '2.5';

    public $module = array(
        'type' => 'equivalent',
        'name' => 'Omeka S',
        'version' => '1.0.0-beta2',
        'size' => 11526232,
        'md5' => '45283a20f3a8e13dac1a9cfaeeaa9c51',
        'url' => 'https://github.com/omeka/omeka-s/releases/download/v%s/omeka-s.zip',
        'requires' => array(
            'minDb' => array(
                'mariadb' => '5.5.3',
                'mysql' => '5.5.3',
            ),
        ),
    );

    /**
     * Define a minimum size for the install directory (without files).
     *
     * @var integer
     */
    public $minOmekaSemanticSize = 100000000;

    /**
     * Define a minimum size for the destination base dir.
     *
     * @var integer
     */
    public $minDestinationSize = 1000000000;

    /**
     * Define a minimum size for the temp directory.
     *
     * @var integer
     */
    public $minTempDirSize = 1000000000;

    /**
     * List of methods to process for the upgrade.
     *
     * @var array
     */
    public $processMethods = array(
        // Installation.
        '_createDirectory',
        '_downloadOmekaS',
        '_unzipOmekaS',
        '_configOmekaS',
        '_installOmekaS',
        '_upgradeLocalConfig',

        // Database.
        '_upgradeUsers',
        '_upgradeSite',
        '_upgradeItemTypes',
        '_upgradeElements',
        // Items are upgraded before collections in order to keep their ids.
        '_upgradeItems',
        '_createItemSetForSite',
        '_upgradeCollections',
        '_setCollectionsOfItems',
        '_upgradeFiles',
        '_upgradeMetadata',

        // Files.
        '_copyFiles',
        '_copyThemes',

        // Specific tasks.
        '_downloadCompatibilityModule',
        '_unzipCompatibiltyModule',
        '_installCompatibiltyModule',
    );

    /**
     * Initialized during init via libraries/data/mapping_files.php.
     *
     * @var array
     */
    // public  $mapping_files = array();

    /**
     * Initialized during init via libraries/data/mapping_roles.php.
     *
     * @var array
     */
    // public $mapping_roles = array();

    /**
     * Initialized during init via libraries/data/mapping_item_types.php.
     *
     * @var array
     */
    // public $mapping_item_types = array();

    /**
     * Initialized during init via libraries/data/mapping_elements.php.
     *
     * @var array
     */
    // public $mapping_elements = array();

    protected $_tables_omekas = array(
        'api_key', 'asset', 'item', 'item_item_set', 'item_set', 'job', 'media',
        'migration', 'module', 'password_creation', 'property', 'resource',
        'resource_class', 'resource_template', 'resource_template_property',
        'session', 'setting', 'site', 'site_block_attachment', 'site_item_set',
        'site_page', 'site_page_block', 'site_permission', 'site_setting',
        'user', 'value', 'vocabulary',
    );

    /**
     * The mapping of record types between Omeka C and Omeka S.
     *
     * @var array
     */
    protected $_mappingRecordTypes = array(
        'Item' => 'item',
        'Collection' => 'item_set',
        'File' => 'media',
        'ElementText' => 'value',
    );

    /**
     * The mapping between classes between Omeka C and Omeka S.
     *
     * @var array
     */
    protected $_mappingRecordClasses = array(
        'Item' => 'Omeka\Entity\Item',
        'Collection' => 'Omeka\Entity\ItemSet',
        'File' => 'Omeka\Entity\Media',
    );

    /**
     * The target default local config.
     *
     * @internal Because the target config uses a namespace for priority, it
     * can't be directly processed and a compatibility with old versions of
     * Omeka and PHP is needed. The priority is hacked when the file is written.
     *
     * @var array
     */
    protected $_omekaSLocalConfig = array(
        'logger' => array(
            'log' => false,
            'priority' => '\Zend\Log\Logger::NOTICE',
        ),
        'http_client' => array(
            'sslcapath' => null,
            'sslcafile' => null,
        ),
        'cli' => array(
            'phpcli_path' => null,
        ),
        'file_manager' => array(
            'thumbnailer' => 'Omeka\File\ImageMagickThumbnailer',
            'thumbnail_types' => array(
                'large' => array('constraint' => 800),
                'medium' => array('constraint' => 200),
                'square' => array('constraint' => 200),
            ),
            'thumbnail_options' => array(
                'imagemagick_dir' => null,
            ),
        ),
        'translator' => array(
            'locale' => 'en_US',
        ),
    );

    /**
     * The target extension whitelist.
     *
     * Removed from the default white list of Omeka 2: "audio/x-m4a",
     * "video/x-m4v" and "video/webm".
     *
     * @var array
     */
    protected $_omekaSMediaTypeWhitelist = array(
        'application/msword', 'application/ogg', 'application/pdf',
        'application/rtf', 'application/vnd.ms-access',
        'application/vnd.ms-excel', 'application/vnd.ms-powerpoint',
        'application/vnd.ms-project', 'application/vnd.ms-write',
        'application/vnd.oasis.opendocument.chart',
        'application/vnd.oasis.opendocument.database',
        'application/vnd.oasis.opendocument.formula',
        'application/vnd.oasis.opendocument.graphics',
        'application/vnd.oasis.opendocument.presentation',
        'application/vnd.oasis.opendocument.spreadsheet',
        'application/vnd.oasis.opendocument.text',
        'application/x-ms-wmp', 'application/x-ogg', 'application/x-gzip',
        'application/x-msdownload', 'application/x-shockwave-flash',
        'application/x-tar', 'application/zip', 'audio/aac', 'audio/aiff',
        'audio/mid', 'audio/midi', 'audio/mp3', 'audio/mp4', 'audio/mpeg',
        'audio/mpeg3', 'audio/ogg', 'audio/wav', 'audio/wma', 'audio/x-aac',
        'audio/x-aiff', 'audio/x-midi', 'audio/x-mp3', 'audio/x-mp4',
        'audio/x-mpeg', 'audio/x-mpeg3', 'audio/x-mpegaudio', 'audio/x-ms-wax',
        'audio/x-realaudio', 'audio/x-wav', 'audio/x-wma', 'image/bmp',
        'image/gif', 'image/icon', 'image/jpeg', 'image/pjpeg', 'image/png',
        'image/tiff', 'image/x-icon', 'image/x-ms-bmp', 'text/css',
        'text/plain', 'text/richtext', 'text/rtf', 'video/asf', 'video/avi',
        'video/divx', 'video/mp4', 'video/mpeg', 'video/msvideo',
        'video/ogg', 'video/quicktime', 'video/x-ms-wmv', 'video/x-msvideo',
    );

    /**
     * The target extension whitelist.
     *
     * Removed from the default white list of Omeka 2: "m4v", "opus" and "webm".
     *
     * @var array
     */
    protected $_omekaSExtensionWhitelist = array(
        'aac', 'aif', 'aiff', 'asf', 'asx', 'avi', 'bmp', 'c', 'cc', 'class',
        'css', 'divx', 'doc', 'docx', 'exe', 'gif', 'gz', 'gzip', 'h', 'ico',
        'j2k', 'jp2', 'jpe', 'jpeg', 'jpg', 'm4a', 'mdb', 'mid', 'midi', 'mov',
        'mp2', 'mp3', 'mp4', 'mpa', 'mpe', 'mpeg', 'mpg', 'mpp', 'odb', 'odc',
        'odf', 'odg', 'odp', 'ods', 'odt', 'ogg', 'pdf', 'png', 'pot', 'pps',
        'ppt', 'pptx', 'qt', 'ra', 'ram', 'rtf', 'rtx', 'swf', 'tar', 'tif',
        'tiff', 'txt', 'wav', 'wax', 'wma', 'wmv', 'wmx', 'wri', 'xla', 'xls',
        'xlsx', 'xlt', 'xlw', 'zip',
    );

    /**
     * Store the full archive size (directory "files").
     *
     * @var integer
     */
    protected $_archiveSize;

    /**
     * Store the full database size.
     *
     * @var integer
     */
    protected $_databaseSize;

    /**
     * Store the free size of the destination directory.
     *
     * @var integer
     */
    protected $_destinationFreeSize;

    /**
     * Store the current mapping of record ids between Omeka C and Omeka S.
     *
     * @var array
     */
    protected $_mappingIds = array();

    protected function _init()
    {
        $dataDir = dirname(dirname(dirname(dirname(__FILE__))))
            . DIRECTORY_SEPARATOR . 'libraries'
            . DIRECTORY_SEPARATOR . 'data';

        $script = $dataDir
            . DIRECTORY_SEPARATOR . 'mapping_files.php';
        $this->mapping_files = require $script;

        $script = $dataDir
            . DIRECTORY_SEPARATOR . 'mapping_roles.php';
        $this->mapping_roles = require $script;

        $script = $dataDir
        . DIRECTORY_SEPARATOR . 'mapping_item_types.php';
        $this->mapping_item_types = require $script;

        $script = $dataDir
            . DIRECTORY_SEPARATOR . 'mapping_elements.php';
        $this->mapping_elements = require $script;
    }

    /**
     * Check if the plugin is installed.
     *
     * @internal Always true for the Core.
     *
     * @return boolean
     */
    public function isPluginReady()
    {
        return true;
    }

    /**
     * @todo Load all the config checks from Omeka Semantic.
     *
     * {@inheritDoc}
     * @see UpgradeToOmekaS_Processor_Abstract::_precheckConfig()
     * @see application/config/module.config.php['installer']['pre_tasks']
     */
    protected function _precheckConfig()
    {
        $this->_precheckVersion();
        $settings = $this->_getSecurityIni();
        if ($settings->precheck->background_dispatcher) {
            $this->_precheckBackgroundDispatcher();
        }
        // During the background process, the server is not Apache.
        if (!$this->_isProcessing) {
            $this->_precheckServer();
        }
        // See Omeka S ['installer']['pre_tasks']: CheckEnvironmentTask.php
        $this->_precheckPhp();
        $this->_precheckPhpModules();
        // See Omeka S ['installer']['pre_tasks']: CheckDbConfigurationTask.php
        $this->_precheckDatabaseServer();
        $this->_precheckZip();
        // Don't check the jobs during true process.
        if (!$this->_isProcessing) {
            $this->_precheckJobs();
        }
    }

    protected function _checkConfig()
    {
        $this->_checkDatabase();
        // See Omeka S ['installer']['pre_tasks']: CheckDirPermissionsTask.php
        $this->_checkFileSystem();
        $this->_checkFreeSize();
    }

    /* Prechecks. */

    protected function _precheckVersion()
    {
        if (version_compare($this->minVersion, OMEKA_VERSION, '>')) {
            $this->_prechecks[] = __('The current release requires at least Omeka %s, current is only %s.',
                $this->minVersion, OMEKA_VERSION);
        }

        if (version_compare($this->maxVersion, OMEKA_VERSION, '<')) {
            $this->_prechecks[] = __('The current release requires at most Omeka %s, current is %s.',
                $this->maxVersion, OMEKA_VERSION);
        }
    }

    protected function _precheckBackgroundDispatcher()
    {
        $config = Zend_Registry::get('bootstrap')->config;
        if ($config) {
            if (isset($config->jobs->dispatcher->longRunning)) {
                if ($config->jobs->dispatcher->longRunning == 'Omeka_Job_Dispatcher_Adapter_Synchronous') {
                    $this->_prechecks[] = __('The process should be done in the background: modify the setting "jobs.dispatcher.longRunning" in the config of Omeka Classic.')
                        . ' ' . __('This precheck may be bypassed via "security.ini".');
                }
            }
            // No long job.
            else {
                $this->_prechecks[] = __('The background job config is not defined in the config of Omeka Classic.');
            }
        }
        // No config.
        else {
            $this->_prechecks[] = __('The config of Omeka Classic has not been found.');
        }
    }

    protected function _precheckServer()
    {
        if ($this->_isServerWindows()) {
            $this->_prechecks[] = __('According to the readme of Omeka Semantic, the server should be a Linux one.');
        }

        if (!$this->_isServerApache()) {
            $this->_prechecks[] = __('According to the readme of Omeka Semantic, the server should be an Apache one.');
        }
    }

    protected function _precheckPhp()
    {
        if (version_compare(PHP_VERSION, '5.6', '<')) {
            $this->_prechecks[] = __('Omeka Semantic requires at least PHP 5.6 and prefers the last stable version.');
        }
        // TODO Add a check for the vesion of PHP in background process?
    }

    protected function _precheckPhpModules()
    {
        $requiredExtensions = array(
            'pdo',
            'pdo_mysql',
        );
        foreach ($requiredExtensions as $extension) {
            if (!extension_loaded($extension)) {
                // TODO Check under Windows.
                if (!function_exists('dl') || !dl($extension . '.so')) {
                    $this->_prechecks[] = __('Omeka Semantic requires the php extension "%s".', $extension);
                }
            }
        }
    }

    protected function _precheckDatabaseServer()
    {
        $sql = 'SHOW VARIABLES LIKE "version"';
        $result = $this->_db->query($sql)->fetchAll();
        if (empty($result)) {
            $this->_prechecks[] = __('The version of the database server cannot be checked.');
        }
        // Standard server.
        else {
            $result = strtolower($result[0]['Value']);
            $mariadb = strpos($result, '-mariadb');
            $version = strtok($result, '-');
            if ($mariadb) {
                $result = version_compare($this->module['requires']['minDb']['mariadb'], $version, '>');
            }
            // Probably a mysql database.
            else {
                $result = version_compare($this->module['requires']['minDb']['mysql'], $version, '>');
            }
            if ($result) {
                $this->_prechecks[] = __('The current release requires at least MariaDB %s or Mysql %s, current is only %s.',
                    $this->module['requires']['minDb']['mariadb'], $this->module['requires']['minDb']['mysql'], ($mariadb ? 'MariaDB' : 'MySQL') . ' ' . $version);
            }
        }
    }

    protected function _precheckZip()
    {
        if (!class_exists('ZipArchive')) {
            try {
                $messageError = __('Zip (as an available command line tool or as the php module ZipArchive) is required to extract downloaded packages.');
                UpgradeToOmekaS_Common::executeCommand('unzip', $status, $output, $errors);
                // A return value of 0 indicates the convert binary is working correctly.
                if ($status != 0) {
                    $this->_prechecks[] = $messageError;
                    $this->_prechecks[] = __('The shell returns an error: %s', $errors);
                }
            } catch (Exception $e) {
                $this->_prechecks[] = $messageError;
                $this->_prechecks[] = __('An error occurs: %s', $e->getMessage());
            }
        }
    }

    protected function _precheckJobs()
    {
        $totalRunningJobs = $this->_db->getTable('Process')
            ->count(array('status' => array(Process::STATUS_STARTING, Process::STATUS_IN_PROGRESS)));
        if ($totalRunningJobs) {
            // Plural needs v2.3.1.
            $this->_prechecks[] = function_exists('plural')
                ? __(plural('%d job is running.', '%d jobs are running.',
                    $totalRunningJobs), $totalRunningJobs)
                : __('%d jobs are running.', $totalRunningJobs);
        }
    }

    protected function _precheckIntegrity()
    {
        $settings = $this->_getSecurityIni();
        if ($settings->precheck->integrity->users) {
            $this->_precheckIntegrityUsers();
        }
        if ($settings->precheck->integrity->files) {
            $this->_precheckIntegrityFiles();
        }
    }

    protected function _precheckIntegrityUsers()
    {
        $db = $this->_db;
        $mappingRoles = $this->getMerged('mapping_roles');
        $roles = array_keys($mappingRoles);
        $table = $db->getTable('User');
        $totalRecords = total_records('User');
        $select = $table->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('id', 'role'))
            ->order('role')
            ->distinct();
        if ($roles) {
            $select
                ->where('role NOT IN (?)', $roles);
        }
        $unmanagedUsers = $table->fetchPairs($select);
        if ($unmanagedUsers) {
            $this->_prechecks[] = __('Some users (%d/%d) have an unmanaged role ("%s") and can’t be upgraded.',
                count($unmanagedUsers), $totalRecords, implode('", "', array_unique($unmanagedUsers)))
                . ' ' . __('This precheck can be bypassed via security.ini.');
        }
    }

    protected function _precheckIntegrityFiles()
    {
        // TODO Get the path from the config.
        $path = FILES_DIR . DIRECTORY_SEPARATOR . 'original';
        $totalFiles = UpgradeToOmekaS_Common::countFilesInDir($path);

        $totalRecords = total_records('File');
        if ($totalFiles > $totalRecords) {
            $this->_prechecks[] = __('There are %d files in the directory "files/original", but only %d are referenced in the database.',
                $totalFiles, $totalRecords)
                . ' ' . __('This precheck can be bypassed via security.ini.');
        }
        elseif ($totalFiles < $totalRecords) {
            $this->_prechecks[] = __('There are only %d files in the directory "files/original", but %d are referenced in the database.',
                $totalFiles, $totalRecords)
                . ' ' . __('This precheck can be bypassed via security.ini.');
        }
    }

    /* Checks. */

    protected function _checkDatabase()
    {
        // TODO Merge with the checks of getTargetDb().

        // Get the database name.
        $db = $this->_db;
        $config = $db->getAdapter()->getConfig();
        $currentDbName = $config['dbname'];
        if (empty($currentDbName)) {
            $this->_checks[] = __('Unable to get the current database name.');
            return;
        }
        $currentDbHost = $config['host'];
        if (empty($currentDbHost)) {
            $this->_checks[] = __('Unable to get the current database host.');
            return;
        }

        // Get size of the current database.
        $sql = 'SELECT SUM(data_length + index_length) AS "Size"
        FROM information_schema.TABLES
        WHERE table_schema = "' . $currentDbName . '";';
        $sizeDatabase = $db->fetchOne($sql);

        // Get snaky free size of the current database.
        $sql = 'SELECT SUM(data_free) AS "Free Size"
        FROM information_schema.TABLES
        WHERE table_schema = "' . $currentDbName . '";';
        $freeSizeDatabase = $db->fetchOne($sql);

        $databaseSize = $sizeDatabase + $freeSizeDatabase;
        $this->_databaseSize = $databaseSize;
        if (empty($sizeDatabase) || empty($databaseSize)) {
            $this->_checks[] = __('Cannot evaluate the size of the Omeka Classic database.');
        }

        // Check if matching params.
        $database = $this->getParam('database');
        if (!isset($database['type'])) {
            $this->_checks[] = __('The type of the database is not defined.');
        }
        $type = $database['type'];
        switch ($type) {
            case 'separate':
                $host = isset($database['host']) ? $database['host'] : '';
                $port = isset($database['port']) ? $database['port'] : '';
                $username = isset($database['username']) ? $database['username'] : '';
                $password = isset($database['password']) ? $database['password'] : '';
                $dbname = isset($database['dbname']) ? $database['dbname'] : '';
                if (empty($host)) {
                    $this->_checks[] = __('The param "%s" should be set when the databases are separate.', 'host');
                }
                if (empty($username)) {
                    $this->_checks[] = __('The param "%s" should be set when the databases are separate.', 'username');
                }
                if (empty($dbname)) {
                    $this->_checks[] = __('The param "%s" should be set when the databases are separate.', 'dbname');
                }
                if ($dbname == $currentDbName && $host == $currentDbHost) {
                    $this->_checks[] = __('The database name should be different from the Omeka Classic one when the databases are separate, but on the same server.');
                }

                // Check access rights.
                $params=array(
                    'host' => $host,
                    'username' => $username,
                    'password' => $password,
                    'dbname' => $dbname,
                );
                if ($port) {
                    $params['port'] = $port;
                }

                try {
                    $targetDb = Zend_Db::Factory('PDO_MYSQL', $params);
                    if (empty($targetDb)) {
                        $this->_checks[] = __('Can’t get access to the database "%s": %s', $dbname, $e->getMessage());
                    }
                } catch (Exception $e) {
                    $this->_checks[] = __('Cannot access to the database "%s": %s', $dbname, $e->getMessage());
                    return;
                }

                $sql = 'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = "' . $dbname . '";';
                $result = $targetDb->fetchOne($sql);
                if ($result) {
                    $this->_checks[] = __('The database "%s" should be empty.', $dbname);
                    return;
                }
                break;

                // The database is shared; so the prefix should be different.
            case 'share':
                $prefix = $database['prefix'];
                if (empty($prefix)) {
                    $this->_checks[] = __('A database prefix is required when the database is shared.');
                    return;
                }
                if ($prefix == $db->prefix) {
                    $this->_checks[] = __('The database prefix should be different from the Omeka Classic one when the database is shared.');
                    return;
                }

                // Check the names of the tables and the prefix.
                $sql = 'SHOW TABLES;';
                $result = $db->fetchCol($sql);
                if (empty($result)) {
                    $this->_checks[] = __('Cannot get the list of the tables of Omeka Classic.');
                    return;
                }
                $existings = array_filter($result, function ($v) use ($prefix) {
                    return strpos($v, $prefix) === 0;
                });
                if ($existings) {
                    $this->_checks[] = __('The prefix "%s" cannot be used, because it causes a conflict in the table names of Omeka Classic.', $prefix);
                    return;
                }

                // Check conflicts of table names.
                $tablesOmekas = $this->getMerged('_tables_omekas');
                if (array_intersect($result, $tablesOmekas)) {
                    $this->_checks[] = __('Some names of tables of Omeka S or its modules are existing in the database of Omeka Classic.');
                }
                break;

            default:
                $this->_checks[] = __('The type of database "%s" is not supported.', $type);
                return;
        }

        // Check max database size.
        // TODO Check max database size. Currently, this is done partially via
        // the check of the size of the filesystem, but the database may be
        // mounted differently or externalized, so some cases can't be managed.
    }

    protected function _checkFileSystem()
    {
        $path = $this->getParam('base_dir');

        // The dir is already validated by the form, but this is an important
        // param and revalidation is quick. The checks are important in
        // particular because the document root may be changed between a web
        // request (the one set by Apache) and a command line request (empty, so
        // saved from document root during install).
        if (!UpgradeToOmekaS_Form_Validator::validateBaseDir($path)) {
            $this->_checks[] = __('The base dir "%s" is not empty, not allowed or not writable.', $path);
            // Other checks are not processed when this one fails.
            return;
        }

        // Check access rights inside the directory, in particular when the
        // directory preexists.
        $isCreated = !file_exists($path);
        if ($isCreated) {
            $result = UpgradeToOmekaS_Common::createDir($path);
            if (empty($result)) {
                $this->_checks[] = __('The base dir "%s" is not writable.', $path);
                return;
            }
        }

        // Check creation of a sub directory.
        $testDir = $path . DIRECTORY_SEPARATOR . 'testdir';
        $result = UpgradeToOmekaS_Common::createDir($testDir);
        if (empty($result)) {
            $this->_checks[] = __('The base dir "%s" is not usable.', $path);
            UpgradeToOmekaS_Common::removeDir($isCreated ? $path : $testDir, true);
            return;
        }

        // Check creation of a file.
        $testFile = $testDir . DIRECTORY_SEPARATOR . md5(rtrim(strtok(substr(microtime(), 2), ' '), '0'));
        $result = touch($testFile);
        if (empty($result)) {
            $this->_checks[] = __('The base dir "%s" does not creation of files.', $path);
            UpgradeToOmekaS_Common::removeDir($isCreated ? $path : $testDir, true);
            return;
        }

        // Check hard linking if needed. This is important when the dir is
        // different from the Omeka Classic one.
        $type = $this->getParam('files_type');
        if ($type == 'hard_link') {
            $testLink = $testDir . DIRECTORY_SEPARATOR . md5(rtrim(strtok(substr(microtime(), 2), ' '), '0'));
            $result = link($testFile, $testLink);
            if (empty($result)) {
                $this->_checks[] = __('The base dir "%s" does not allow creation of hard links.', $path);
                UpgradeToOmekaS_Common::removeDir($isCreated ? $path : $testDir, true);
                return;
            }
        }

        // Get free size on the temp folder.
        $tempDir = sys_get_temp_dir();
        $result = disk_free_space($tempDir);
        if ($result < $this->minTempDirSize) {
            $this->_checks[] = __('The free size of the temp directory should be greater than %dMB.', ceil($this->minTempDirSize / 1000000));
            UpgradeToOmekaS_Common::removeDir($isCreated ? $path : $testDir, true);
            return;
        }

        // Get free size on the destination file sytem.
        $result = disk_free_space($path);
        if ($result < $this->minDestinationSize) {
            $this->_checks[] = __('The free size of the base dir should be greater than %dMB.', ceil($this->minDestinationSize / 1000000));
            UpgradeToOmekaS_Common::removeDir($isCreated ? $path : $testDir, true);
            return;
        }
        $this->_destinationFreeSize = $result;

        // Get current size of the files folder.
        $result = UpgradeToOmekaS_Common::getDirectorySize(FILES_DIR);
        if (empty($result)) {
            $this->_checks[] = __('Cannot evaluate the size of the Omeka Classic files dir.');
            UpgradeToOmekaS_Common::removeDir($isCreated ? $path : $testDir, true);
            return;
        }
        $this->_archiveSize = $result;

        UpgradeToOmekaS_Common::removeDir($isCreated ? $path : $testDir, true);
    }

    protected function _checkFreeSize()
    {
        $archiveSize = $this->_archiveSize;
        $databaseSize = $this->_databaseSize;
        $destinationFreeSize = $this->_destinationFreeSize;

        if (empty($archiveSize)) {
            $this->_checks[] = __('The size of the archive can’t be determined.');
            return;
        }
        if (empty($databaseSize)) {
            $this->_checks[] = __('The size of the database can’t be determined.');
            return;
        }
        if (empty($destinationFreeSize)) {
            $this->_checks[] = __('The free space size can’t be determined.');
            return;
        }

        $type = $this->getParam('files_type');
        switch ($type) {
            case 'copy':
                $minDestinationSize = 1.2 * $archiveSize + $this->minOmekaSemanticSize;
                break;
            case 'hard_link':
                $numberFiles = UpgradeToOmekaS_Common::countFilesInDir(FILES_DIR);
                $minDestinationSize = 5000 * $numberFiles + $this->minOmekaSemanticSize;
                break;
            case 'dummy':
                $numberFiles = UpgradeToOmekaS_Common::countFilesInDir(FILES_DIR);
                $minDestinationSize = 10000 * $numberFiles + $this->minOmekaSemanticSize;
                break;
            default:
                $this->_checks[] = __('The type of files "%s" is unknown.', $type);
                return;
        }

        if ($destinationFreeSize < $minDestinationSize) {
            $this->_checks[] = __('A minimum size of %dMB is required in the base dir, only %dMB is available.',
                ceil($minDestinationSize / 1000000), ceil($destinationFreeSize / 1000000));
            return;
        }

        // TODO Check when the file systems of the database and files are different.

        $minSize = $minDestinationSize + 2 * $databaseSize;
        if ($destinationFreeSize < $minSize) {
            $this->_checks[] = __('A minimum size of %dMB (%dMB for the files and %dMB for the database) is required in the base dir, only %dMB is available.',
                ceil($minSize / 1000000), ceil($minDestinationSize / 1000000), ceil($databaseSize / 1000000), ceil($destinationFreeSize / 1000000));
            return;
        }
    }

    /**
     * Helper to get the server OS.
     *
     * @return string
     */
    protected function _isServerWindows()
    {
        return strncasecmp(PHP_OS, 'WIN', 3) == 0;
    }

    /**
     * Helper to get the server OS.
     *
     * @return string
     */
    protected function _isServerApache()
    {
        $serverSofware = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '';
        return strpos(strtolower($serverSofware), 'apache') === 0;
    }

    /* Methods for the upgrade. */

    protected function _downloadOmekaS()
    {
        $this->_downloadModule();
    }

    protected function _unzipOmekaS()
    {
        $this->_unzipModule();
    }

    protected function _configOmekaS()
    {
        // Load and check the database.
        $targetDb = $this->getTargetDb();

        // Create database.ini.
        $database = $this->getParam('database');
        if (!isset($database['type'])) {
            throw new UpgradeToOmekaS_Exception(
                __('The type of the database is not defined.', $type));
        }
        $type = $database['type'];
        switch ($type) {
            case 'separate':
                $host = isset($database['host']) ? $database['host'] : '';
                $port = isset($database['port']) ? $database['port'] : '';
                $username = isset($database['username']) ? $database['username'] : '';
                $password = isset($database['password']) ? $database['password'] : '';
                $dbname = isset($database['dbname']) ? $database['dbname'] : '';
                // $prefix = isset($database['prefix']) ? $database['prefix'] : '';
                break;

            case 'share':
                $db = $this->_db;
                $config = $db->getAdapter()->getConfig();
                $host = isset($config['host']) ? $config['host'] : '';
                $port = isset($config['port']) ? $config['port'] : '';
                $dbname = isset($config['dbname']) ? $config['dbname'] : '';
                $username = isset($config['username']) ? $config['username'] : '';
                $password = isset($config['password']) ? $config['password'] : '';
                // $prefix = isset($config['prefix']) ? $config['prefix'] : '';
                break;

            default:
                throw new UpgradeToOmekaS_Exception(
                    __('The type "%s" is not possible for the database.', $type));
        }

        $databaseConfig = 'user     = "' . $username . '"'. PHP_EOL;
        $databaseConfig .= 'password = "' . $password . '"'. PHP_EOL;
        $databaseConfig .= 'dbname   = "' . $dbname . '"'. PHP_EOL;
        $databaseConfig .= 'host     = "' . $host . '"'. PHP_EOL;
        // $databaseConfig .= empty($prefix)
        //     ? ';prefix   = '. PHP_EOL
        //     : 'prefix   = "' . $prefix . '"'. PHP_EOL;
        $databaseConfig .= empty($port)
            ? ';port     = '. PHP_EOL
            : 'port     = "' . $port . '"'. PHP_EOL;
        $databaseConfig .= ';unix_socket = "' . '' . '"'. PHP_EOL;
        $databaseConfig .= ';log_path = "' . '' . '"'. PHP_EOL;

        $databaseIni = $this->getFullPath('config/database.ini');
        $result = file_put_contents($databaseIni, $databaseConfig);
        if (empty($result)) {
            throw new UpgradeToOmekaS_Exception(
                __('Unable to save file "%s".', 'database.ini'));
        }

        $this->_log('[' . __FUNCTION__ . ']: ' . __('The file "config/database.ini" has been updated successfully.'),
            Zend_Log::INFO);
    }

    /**
     * @see application/config/module.config.php['installer']['tasks']
     * @throws UpgradeToOmekaS_Exception
     */
    protected function _installOmekaS()
    {
        $targetDb = $this->getTargetDb();

        // See Omeka S ['installer']['tasks']: DestroySessionTask.php
        // Nothing to do: there is no session by default in the tables and no
        // user is logged since it is processed automatically.
        $this->_log('[' . __FUNCTION__ . ']: ' . __('Installer Task "%s" ended (nothing to do).', 'Destroy Session'),
            Zend_Log::DEBUG);

        // See Omeka S ['installer']['tasks']: ClearCacheTask.php
        // Nothing to do.
        $this->_log('[' . __FUNCTION__ . ']: ' . __('Installer Task "%s" ended (nothing to do).', 'Clear Cache'),
            Zend_Log::DEBUG);

        // See Omeka S ['installer']['tasks']: InstallSchemaTask.php
        // The Omeka S schema is an optimized sql script, so use it.
        $script = $this->getFullPath('application/data/install/schema.sql');
        $sql = file_get_contents($script);
        $result = $targetDb->prepare($sql)->execute();
        if (!$result) {
            throw new UpgradeToOmekaS_Exception(
                __('Unable to execute install queries.'));
        }
        $this->_log('[' . __FUNCTION__ . ']: ' . __('Installer Task "%s" ended.', 'Install Schema'),
            Zend_Log::DEBUG);

        // See Omeka S ['installer']['tasks']: RecordMigrationsTask.php
        // See Omeka\Db\Migration\Manager::getAvailableMigrations()
        // Omeka 2 should be compatible with 5.3.2.
        $path = $this->getFullPath('application/data/migrations');
        $migrations = array();
        $globPattern = $path . DIRECTORY_SEPARATOR . '*.php';
        $regexPattern = '/^(\d+)_(\w+)\.php$/';
        $files = glob($globPattern, GLOB_MARK);
        foreach ($files as $filename) {
            if (preg_match($regexPattern, basename($filename), $matches)) {
                $version = $matches[1];
                $migrations[] = $version;
            }
        }
        $sql = 'INSERT INTO migration VALUES(' . implode('),(', $migrations) . ');';
        $result = $targetDb->prepare($sql)->execute();
        if (!$result) {
            throw new UpgradeToOmekaS_Exception(
                __('Unable to update list of migrations.'));
        }
        $this->_log('[' . __FUNCTION__ . ']: ' . __('Installer Task "%s" ended.', 'Record Migrations'),
            Zend_Log::DEBUG);

        // See Omeka S ['installer']['tasks']: InstallDefaultVocabulariesTask.php
        // To simplify process for vocabularies, that Omeka doesn't manage, an
        // export of a fresh automatic install is used (the installer task
        // imports rdf vocabularies in application/data/vocabularies).
        $script = dirname(dirname(dirname(dirname(__FILE__))))
            . DIRECTORY_SEPARATOR . 'libraries'
            . DIRECTORY_SEPARATOR . 'data'
            . DIRECTORY_SEPARATOR . 'rdf_vocabularies.sql';
        $sql = file_get_contents($script);
        $result = $targetDb->prepare($sql)->execute();
        if (!$result) {
            throw new UpgradeToOmekaS_Exception(
                __('Unable to execute install queries for default vocabularies.'));
        }
        $this->_log('[' . __FUNCTION__ . ']: ' . __('Installer Task "%s" ended.', 'Install Default Vocabularies'),
            Zend_Log::DEBUG);

        // See Omeka S ['installer']['tasks']: InstallDefaultTemplatesTask.php
        // Same note than vocabularies above.
        $script = dirname(dirname(dirname(dirname(__FILE__))))
            . DIRECTORY_SEPARATOR . 'libraries'
            . DIRECTORY_SEPARATOR . 'data'
            . DIRECTORY_SEPARATOR . 'default_templates.sql';
        $sql = file_get_contents($script);
        $result = $targetDb->prepare($sql)->execute();
        if (!$result) {
            throw new UpgradeToOmekaS_Exception(
                __('Unable to execute install queries for default templates.'));
        }
        $this->_log('[' . __FUNCTION__ . ']: ' . __('Installer Task "%s" ended.', 'Install Default Templates'),
            Zend_Log::DEBUG);

        // See Omeka S ['installer']['tasks']: CreateFirstUserTask.php
        $this->_log('[' . __FUNCTION__ . ']: ' . __('Installer Task "%s" skipped (processed with other users).', 'Create First User'),
            Zend_Log::DEBUG);

        // See Omeka S ['installer']['tasks']: AddDefaultSettingsTask.php
        $this->_setSetting('version', $this->module['version']);

        // Use the customized value for admin pages if modified.
        $value = get_option('per_page_admin');
        if ($value == 10) {
            // Default of Omeka S.
            $value = 25;
        } else {
            $this->_log('[' . __FUNCTION__ . ']: ' . __('Omeka S doesn’t use a specific pagination value for public pages.'),
                Zend_Log::NOTICE);
        }
        $this->_setSetting('pagination_per_page', $value);

        $value = get_option('file_mime_type_whitelist');
        if ($value == Omeka_Validate_File_MimeType::DEFAULT_WHITELIST) {
            $value = $this->_omekaSMediaTypeWhitelist;
        } else {
            $value = explode(',', $value);
        }
        $this->_setSetting('media_type_whitelist', $value);
        $this->_log('[' . __FUNCTION__ . ']: ' . __('These three media types have been removed from the default white list of Omeka 2: "audio/x-m4a", "video/x-m4v" and "video/webm".'),
            Zend_Log::INFO);

        $value = get_option('file_extension_whitelist');
        if ($value == Omeka_Validate_File_Extension::DEFAULT_WHITELIST) {
            $value = $this->_omekaSExtensionWhitelist;
        } else {
            $value = explode(',', $value);
        }
        $this->_setSetting('extension_whitelist', $value);
        $this->_log('[' . __FUNCTION__ . ']: ' . __('These three extensions have been removed from the default white list of Omeka 2: "m4v", "opus" and "webm".'),
            Zend_Log::INFO);

        $user = $this->getParam('user');
        if (empty($user)) {
            throw new UpgradeToOmekaS_Exception(
                __('No user has been defined.'));
        }
        // Use the option "administrator_email" instead of the current user.
        $value = get_option('administrator_email') ?: $user->email;
        $this->_setSetting('administrator_email', $value);
        $this->_setSetting('installation_title', $this->getParam('installation_title'));
        $this->_setSetting('time_zone', $this->getParam('time_zone'));

        // Settings that are not set when the site is installed.

        // Even if the first site is not yet created.
        $this->_setSetting('default_site', (string) 1);
        $this->_setSetting('disable_file_validation', (string) get_option('disable_default_file_validation'));
        $this->_setSetting('property_label_information', 'none');
        $this->_setSetting('recaptcha_site_key', (string) get_option('recaptcha_public_key'));
        $this->_setSetting('recaptcha_secret_key', (string) get_option('recaptcha_private_key'));
        $this->_setSetting('use_htmlpurifier', (string) get_option('html_purifier_is_enabled'));

        $this->_log('[' . __FUNCTION__ . ']: ' . __('Installer Task "%s" ended.', 'Add Default Settings'),
            Zend_Log::DEBUG);

        $this->_log('[' . __FUNCTION__ . ']: ' . __('The main tables are created and default data inserted.'),
            Zend_Log::INFO);
    }

    protected function _upgradeLocalConfig()
    {
        // Convert config.ini into local.config.php with reasonable assertions.
        $config = Zend_Registry::get('bootstrap')->config;

        // Get default values of the local config of Omeka S.
        $targetConfig = $this->_omekaSLocalConfig;

        // Localization.

        // locale.name = ""
        $value = isset($config->locale->name) ? $config->locale->name : null;
        if ($value) {
            $targetConfig['translator']['locale'] = $value;
        }

        // Debugging.

        // Debug is not set in config.ini, but in the application environment..
        // debug.exceptions = false
        // debug.request = false
        // debug.profileDb = false
        // debug.email = ""
        // debug.emailLogPriority = Zend_Log::ERR
        $flag = false;
        foreach (array(
                'exceptions' => false,
                'request' => false,
                'profileDb' => false,
                'email' => '',
                'emailLogPriority' => 'Zend_Log::ERR',
            ) as $name => $defaultValue) {
            $value = isset($config->debug->$name) ? $config->debug->$name : null;
            if ($value != $defaultValue) {
                $flag = true;
                $this->_log('[' . __FUNCTION__ . ']: ' . __('The parameter "%s" is not supported by Omeka S currently.',
                    'debug.' . $name), Zend_Log::WARN);
            }
        }
        if ($flag) {
            $this->_log('[' . __FUNCTION__ . ']: ' . __('Use the integrated logging system or the environment to debug Omeka S.'),
                Zend_Log::NOTICE);
        }

        // Logging.

        // log.errors = false
        $value = isset($config->log->errors) ? $config->log->errors : null;
        if ($value) {
            $targetConfig['logger']['log'] = (boolean) $value;
        }
        // log.priority = Zend_Log::WARN
        // The priority is kept to NOTICE, except if has been modified.
        $value = isset($config->log->priority) ? $config->log->priority : null;
        if ($value && $value != 'Zend_Log::WARN') {
            $targetConfig['logger']['priority'] = '\Zend\Log\Logger::' . substr($value, 10);
        }
        // log.sql = false
        // This value is not used in Omeka S.
        $value = isset($config->log->sql) ? $config->log->sql : null;
        if ($value) {
            $this->_log('[' . __FUNCTION__ . ']: ' . __('The processor doesn’t convert the parameter "%s" currently.',
                'log.sql'), Zend_Log::WARN);
        }

        // Sessions.
        // TODO Manage the sessions config, but rarely modified.

        // session.name = ""
        $value = isset($config->session->name) ? $config->session->name : null;
        if ($value) {
            $this->_log('[' . __FUNCTION__ . ']: ' . __('The processor doesn’t convert the parameter "%s" currently.',
                'session.name'), Zend_Log::WARN);
        }
        // ; session.saveHandler = ""
        $value = isset($config->session->saveHandler) ? $config->session->saveHandler : null;
        if ($value) {
            $this->_log('[' . __FUNCTION__ . ']: ' . __('The processor doesn’t convert the parameter "%s" currently.',
                'session.saveHandler'), Zend_Log::WARN);
        }

        // Theme.

        // theme.useInternalAssets = false
        $value = isset($config->theme->useInternalAssets) ? $config->theme->useInternalAssets : null;
        if ($value) {
            $targetConfig['assets']['use_externals'] = false;
        }

        // Background Scripts.

        // background.php.path = ""
        $value = isset($config->background->php->path) ? $config->background->php->path : null;
        if ($value) {
            $targetConfig['cli']['phpcli_path'] = $value;
        }
        // jobs.dispatcher.default = "Omeka_Job_Dispatcher_Adapter_Synchronous"
        $value = isset($config->jobs->dispatcher->default) ? $config->jobs->dispatcher->default : null;
        if ($value != 'Omeka_Job_Dispatcher_Adapter_Synchronous') {
            $this->_log('[' . __FUNCTION__ . ']: ' . __('The processor doesn’t convert the parameter "%s" currently.',
                'jobs.dispatcher.default'), Zend_Log::WARN);
        }
        // jobs.dispatcher.longRunning = "Omeka_Job_Dispatcher_Adapter_BackgroundProcess"
        $value = isset($config->jobs->dispatcher->longRunning) ? $config->jobs->dispatcher->longRunning : null;
        if ($value != 'Omeka_Job_Dispatcher_Adapter_BackgroundProcess') {
            $this->_log('[' . __FUNCTION__ . ']: ' . __('The processor doesn’t convert the parameter "%s" currently.',
                'jobs.dispatcher.longRunning'), Zend_Log::WARN);
        }

        // Mail.

        // mail.transport.type = "Sendmail"
        // ; mail.transport.type = "Smtp"
        // ; mail.transport.host = ""
        // ; mail.transport.port = ###     ; Port number, if applicable.
        // ; mail.transport.name = ""      ; Local client hostname, e.g. "localhost"
        // ; mail.transport.auth = "login" ; For authentication, if required.
        // ; mail.transport.username = ""
        // ; mail.transport.password = ""
        // ; mail.transport.ssl = ""       ; For SSL support, set to "ssl" or "tls"
        $value = isset($config->mail->transport->type) ? $config->mail->transport->type : null;
        $values = isset($config->mail->transport) ? $config->mail->transport->toArray() : array();
        unset($values['type']);
        switch ($value) {
            case 'Sendmail':
                // Nothing to do: this is the default in Omeka S too.
                break;
            case 'Smtp':
            default:
                // TODO Check if options have the same keys.
                $targetConfig['mail']['transport']['type'] = strtolower($value);
                $targetConfig['mail']['transport']['options'] = $values;
                break;
        }
        if ($values) {
            $this->_log('[' . __FUNCTION__ . ']: ' . __('The config used to send mail should be checked.'),
                Zend_Log::NOTICE);
        }

        // Storage.

        // ; storage.adapter = "Omeka_Storage_Adapter_ZendS3"
        // ; storage.adapterOptions.accessKeyId =
        // ; storage.adapterOptions.secretAccessKey =
        // ; storage.adapterOptions.bucket =
        // ; storage.adapterOptions.expiration = 10 ; URL expiration time (in minutes)
        // ; storage.adapterOptions.endpoint = ; Custom S3 endpoint (optional)
        $value = isset($config->storage->adapter) ? $config->storage->adapter : null;
        $values = isset($config->storage->adapterOptions) ? $config->storage->adapterOptions->toArray() : array();
        if ($value || $values) {
            $this->_log('[' . __FUNCTION__ . ']: ' . __('The processor doesn’t convert the parameter "%s" currently.',
                'storage.adapter'), Zend_Log::WARN);
        }

        // Security.

        // ; ssl = "always"
        $value = isset($config->ssl) ? $config->ssl : null;
        if ($value) {
            $allowedValues = array('logins', 'sessions', 'always');
            $this->_log('[' . __FUNCTION__ . ']: ' . __('The processor doesn’t convert the parameter "%s" currently.',
                'ssl'), Zend_Log::WARN);
        }

        // Upload.

        // ;upload.maxFileSize = "10M"
        $value = isset($config->upload->maxFileSize) ? $config->upload->maxFileSize : null;
        if ($value && $value != '10M') {
            $this->_log('[' . __FUNCTION__ . ']: ' . __('The parameter "%s" is not supported by Omeka S currently.',
                'upload.maxFileSize'), Zend_Log::WARN);
            $this->_log('[' . __FUNCTION__ . ']: ' . __('Update yourself the config of the server (php.ini), or set the value in ".htaccess".'),
                Zend_Log::NOTICE);
        }

        // Derivative Images.

        // ;fileDerivatives.strategy = "Omeka_File_Derivative_Strategy_ExternalImageMagick"
        $value = isset($config->fileDerivatives->strategy) ? $config->fileDerivatives->strategy : 'Omeka_File_Derivative_Strategy_ExternalImageMagick';
        switch ($value) {
            case 'Omeka_File_Derivative_Strategy_ExternalImageMagick':
                // Nothing to do: this is the default in Omeka S too.
                break;
            case 'Omeka_File_Derivative_Strategy_Imagick':
                $targetConfig['file_manager']['thumbnailer'] = 'Omeka\File\ImagickThumbnailer';
                break;
            case 'Omeka_File_Derivative_Strategy_GD':
                $targetConfig['file_manager']['thumbnailer'] = 'Omeka\File\GdThumbnailer';
                break;
            default:
                $this->_log('[' . __FUNCTION__ . ']: ' . __('The derivative strategy "%s" is not supported by Omeka S.',
                    $value), Zend_Log::WARN);
        }

        $values = isset($config->fileDerivativesy->strategyOptions) ? $config->fileDerivativesy->strategyOptions->toArray() : null;
        // ; fileDerivatives.strategyOptions.page = "0"
        if (isset($values['page']) && $values['page'] !== '0') {
            $targetConfig['file_manager']['thumbnail_options']['page'] = (integer) $values['page'];
        }
        // ; fileDerivatives.strategyOptions.gravity = "center"
        if (isset($values['gravity']) && $values['gravity'] !== 'center') {
            $targetConfig['file_manager']['thumbnail_types']['square']['options']['gravity'] = $values['gravity'];
        }
        // ; fileDerivatives.strategyOptions.autoOrient = false
        if (isset($values['autoOrient']) && $values['autoOrient']) {
            $this->_log('[' . __FUNCTION__ . ']: ' . __('The parameter "%s" is not supported by Omeka S currently.',
                'fileDerivatives.strategyOptions.autoOrient'), Zend_Log::WARN);
        }
        // ;fileDerivatives.typeWhitelist[] = "image/jpeg"
        $values = isset($config->fileDerivatives->typeWhitelist) ? $config->fileDerivatives->typeWhitelist->toArray() : null;
        if (!empty($values)) {
            // See "media_type_whitelist" and "extension_whitelist" too.
            $this->_log('[' . __FUNCTION__ . ']: ' . __('The parameter "%s" is not supported by Omeka S currently.',
                'fileDerivatives.typeWhitelist'), Zend_Log::WARN);
        }
        // ;fileDerivatives.typeBlacklist[] = "image/jpeg"
        $values = isset($config->fileDerivatives->typeBlacklist) ? $config->fileDerivatives->typeBlacklist->toArray() : null;
        if (!empty($values)) {
            $this->_log('[' . __FUNCTION__ . ']: ' . __('The parameter "%s" is not supported by Omeka S currently.',
                'fileDerivatives.typeBlacklist'), Zend_Log::WARN);
        }

        // Add some settings options that are moved inside the local config.
        $value = get_option('path_to_convert');
        if ($value) {
            $targetConfig['file_manager']['thumbnail_options']['imagemagick_dir'] = $value;
        }

        // Omeka 2 and Omeka S use the same sizes.
        $derivativeTypes = array(
            'fullsize' => array('type' => 'large', 'constraint' => 800, 'strategy' => 'default'),
            'thumbnail' => array('type' => 'medium', 'constraint' => 200, 'strategy' => 'default'),
            'square_thumbnail' => array('type' => 'square', 'constraint' => 200, 'strategy' => 'square'),
        );
        // This option is used in a fork and allows multiple thumbnail types.
        $values = get_option('derivative_types');
        if ($values) {
            $values = unserialize($values);
            foreach ($derivativeTypes as $derivativeType) {
                // Set default values except for default derivative types.
                if (!isset($derivativeTypes[$derivativeType])) {
                    $derivativeTypes[$derivativeType] = array(
                        'added' => true,
                        'type' => $derivativeType,
                        'constraint' => get_option($derivativeType . '_constraint'),
                        'stragegy' => strpos($derivativeType, 'square') !== false,
                    );
                }
            }
        }
        foreach ($derivativeTypes as $derivativeType => $options) {
            if (!empty($options['added']) && $options['strategy'] != 'default') {
                $targetConfig['file_manager']['thumbnail_types'][$options['type']]['strategy'] = $options['strategy'];
            }
            $value = get_option($derivativeType . '_constraint');
            if ($value != $options['constraint'] || !empty($options['added'])) {
                $targetConfig['file_manager']['thumbnail_types'][$options['type']]['constraint'] = $value;
            }
        }

        // Convert the array into an indented raw file.
        $localConfig = $this->_createRawArray($targetConfig);
        $localConfigPhp = $this->getFullPath('config/local.config.php');
        $result = file_put_contents($localConfigPhp, $localConfig);
        if (empty($result)) {
            throw new UpgradeToOmekaS_Exception(
                __('Unable to save file "%s".', 'local.config.php'));
        }

        // application.ini
        // routes.ini
        // .htaccess
        // errors.log!
        $this->_log('[' . __FUNCTION__ . ']: ' . __('The files "application.ini", "routes.ini", ".htaccess" and "errors.log" are not upgradable.')
            . ' ' . __('Check if you modified them.'), Zend_Log::NOTICE);
    }

    protected function _upgradeUsers()
    {
        $recordType = 'User';
        $db = $this->_db;
        $targetDb = $this->getTargetDb();

        $user = $this->getParam('user');

        $totalRecords = total_records($recordType);

        // Check if there are already records.
        $totalExisting = $this->countTargetTable('user');
        if ($totalExisting) {
            // TODO Allow to upgrade without ids (need a temp mapping of source and destination ids)?
            throw new UpgradeToOmekaS_Exception(
                __('Some users(%d) have been upgraded, so ids won’t be kept.',
                    $totalExisting)
                . ' ' . __('Check the processors of the plugins.'));
        }

        // This case is possible with an external identification (ldap...).
        if (empty($totalRecords)) {
            $id = $user->id;
            $toInsert = array();
            $toInsert['id'] = $id;
            $toInsert['email'] = substr($user->email, 0, 190);
            $toInsert['name'] = substr($user->name, 0, 190);
            $toInsert['created'] = $this->getDatetime();
            $toInsert['modified'] = null;
            $toInsert['password_hash'] = null;
            $toInsert['role'] = 'global_admin';
            $toInsert['is_active'] = 1;
            $result = $targetDb->insert('user', $toInsert);
            if (!$result) {
                throw new UpgradeToOmekaS_Exception(
                    __('Unable to insert the global user.'));
            }
            $this->_mappingIds[$recordType][$user->id] = $id;

            $this->_log('[' . __FUNCTION__ . ']: ' . __('No user to upgrade: only the global administrator has been created from the current user.'),
                Zend_Log::WARN);
        }
        // There is one or more users, at least the current one.
        else {
            $mappingRoles = $this->getMerged('mapping_roles');

            // The process uses the regular queries of Omeka in order to keep
            // only good records.
            $table = $db->getTable('User');

            $totalSupers = 0;
            $totalAdmins = 0;
            $unmanagedRoles = array();

            $loops = floor(($totalRecords - 1) / $this->maxChunk) + 1;
            for ($page = 1; $page <= $loops; $page++) {
                $records = $table->findBy(array(), $this->maxChunk, $page);

                $toInserts = array();
                foreach ($records as $record) {
                    if ($record->role == 'super') {
                        $totalSupers++;
                    } elseif ($record->role == 'admin') {
                        $totalAdmins++;
                    }
                    if (!isset($mappingRoles[$record->role])) {
                        $unmanagedRoles[$record->role] = isset($unmanagedRoles[$record->role])
                            ? ++$unmanagedRoles[$record->role]
                            : 1;
                        continue;
                    }
                    $id = (integer) $record->id;
                    $role = $record->id == $user->id
                        ? 'global_admin'
                        : $mappingRoles[$record->role];
                    $toInsert = array();
                    $toInsert['id'] = $id;
                    $toInsert['email'] = substr($record->email, 0, 190);
                    $toInsert['name'] = substr($record->name, 0, 190);
                    $toInsert['created'] = $this->getDatetime();
                    $toInsert['modified'] = null;
                    $toInsert['password_hash'] = null;
                    $toInsert['role'] = $role;
                    $toInsert['is_active'] = (integer) (boolean) $record->active;
                    $toInserts[] = $this->_dbQuote($toInsert);

                    $this->_mappingIds[$recordType][$record->id] = $id;
                }

                $this->_insertRows('user', $toInserts);
            }

            $totalUnmanaged = array_sum($unmanagedRoles);
            $totalUpgraded = $totalRecords - $totalUnmanaged;
            if ($totalUnmanaged) {
                // Plural needs v2.3.1.
                $unknownRolesString = implode('", "', array_keys($unmanagedRoles));
                $message = function_exists('plural')
                    ? __(plural('%d user not upgraded (role: "%s").', '%d users not upgraded (roles: "%s").',
                        $totalUnmanaged), $totalUnmanaged, $unknownRolesString)
                    : __('%d users not upgraded (roles: "%s").', $totalUnmanaged, $unknownRolesString);
                $this->_log('[' . __FUNCTION__ . ']: ' . $message,
                    Zend_Log::WARN);
            }

            $this->_log('[' . __FUNCTION__ . ']: ' . __('The current user has been made the global administrator.'),
                Zend_Log::NOTICE);
            if ($totalSupers > 1 && $totalAdmins) {
                $message = __('The other super users [%d] have been made site administrators, like the admins [%d].',
                    $totalSupers <= 1 ? 0 : $totalSupers - 1, $totalAdmins);
                $this->_log('[' . __FUNCTION__ . ']: ' . $message,
                    Zend_Log::NOTICE);
            }
            // Plural needs v2.3.1.
            $message = function_exists('plural')
                ? __(plural('%d user upgraded.', '%d users upgraded.', $totalUpgraded), $totalUpgraded)
                : __('%d users upgraded.', $totalUpgraded);
            $this->_log('[' . __FUNCTION__ . ']: ' . $message,
                Zend_Log::NOTICE);
        }

        $settings = $this->_getSecurityIni();
        if (!empty($settings->default->global_admin_password)) {
            $bind = array();
            $bind['password_hash'] = $settings->default->global_admin_password;
            $bind['modified'] = $this->getDatetime();
            $result = $targetDb->update('user', $bind, 'id = ' . $user->id);
        }

        $this->_log('[' . __FUNCTION__ . ']: ' . __('The username of users has been removed; the displayed name is unchanged.'),
            Zend_Log::NOTICE);
        $this->_log('[' . __FUNCTION__ . ']: ' . __('All users must request a new password in the login page.'),
            Zend_Log::NOTICE);
    }

    protected function _upgradeSite()
    {
        // Settings of Omeka Classic: create the first site.
        $db = $this->_db;
        $targetDb = $this->getTargetDb();
        $settings = $this->_getSecurityIni();
        $user = $this->getParam('user');

        $title = $this->getSiteTitle();
        $slug = $this->getSiteSlug();
        $theme = !empty($settings->default->site->theme)
            ? $settings->default->site->theme
            : get_option('public_theme');

        $id = 1;

        $toInsert = array();
        $toInsert['id'] = $id;
        $toInsert['owner_id'] = $this->_mappingIds['User'][$user->id];
        $toInsert['slug'] = $slug;
        $toInsert['theme'] = substr($theme ?: 'default', 0,190);
        $toInsert['title'] = substr($title, 0, 190);
        $toInsert['navigation'] = json_encode($this->_convertNavigation());
        $toInsert['item_pool'] = json_encode(array());
        $toInsert['created'] = $this->getDatetime();
        $toInsert['is_public'] = 1;
        $result = $targetDb->insert('site', $toInsert);
        if (!$result) {
            throw new UpgradeToOmekaS_Exception(
                __('Unable to create the first site.'));
        }

        $this->_siteId = $id;

        $this->_setSiteSetting('attachment_link_type', 'item');
        $this->_setSiteSetting('browse_attached_items', '0');

        // An item set for the site will be created later to keep original ids.

        $totalVisibleLinks = $this->_countNavigationPages(true);
        if ($totalVisibleLinks) {
            $this->_log('[' . __FUNCTION__ . ']: ' . __('%d navigation links have been upgraded.',
                $totalVisibleLinks), Zend_Log::INFO);
        }
        $totalInvisibleLinks = $this->_countNavigationPages(false);
        if ($totalInvisibleLinks) {
            // Plural needs v2.3.1.
            $message = function_exists('plural')
                ? __(plural(
                    'Omeka S doesn’t allow to hide/show navigation links, so %d link has not been upgraded.',
                    'Omeka S doesn’t allow to hide/show navigation links, so %d links have not been upgraded.',
                    $totalInvisibleLinks), $totalInvisibleLinks)
                : __('Omeka S doesn’t allow to hide/show navigation links, so %d links have not been upgraded.',
                    $totalInvisibleLinks);
            $this->_log('[' . __FUNCTION__ . ']: ' . $message,
                Zend_Log::NOTICE);
        }
        $this->_log('[' . __FUNCTION__ . ']: ' . __('The conversion of the navigation is currently partial and some false links may exist.',
            $totalInvisibleLinks), Zend_Log::INFO);

        // Check if SimplePages is active to add the default page or not.
        $processors = $this->getProcessors();
        if (!isset($processors['SimplePages'])) {
            $title = __('Welcome');
            $slug = $this->_slugify($title);
            $toInsert = array();
            $toInsert['id'] = 1;
            $toInsert['site_id'] = 1;
            $toInsert['slug'] = substr($slug, 0, 190);
            $toInsert['title'] = substr($title, 0, 190);
            $toInsert['created'] = $this->getDatetime();
            $result = $targetDb->insert('site_page', $toInsert);

            $toInsert = array();
            $toInsert['page_id'] = 1;
            $toInsert['layout'] = 'pageTitle';
            $toInsert['data'] = $this->_toJson(array());
            $toInsert['position'] = 1;
            $result = $targetDb->insert('site_page_block', $toInsert);

            $data = array(
                'html' => __('Welcome to your new site. This is an example page.')
                    . '<p>' . get_option('description') . '</p>'
                    . '<p>' . __('Author: %s', get_option('author')) . '</p>'
                    . '<p>' . __('Copyright: %s', get_option('copyright')) . '</p>',
            );
            $toInsert = array();
            $toInsert['page_id'] = 1;
            $toInsert['layout'] = 'html';
            $toInsert['data'] = $this->_toJson($data);
            $toInsert['position'] = 2;
            $result = $targetDb->insert('site_page_block', $toInsert);
        }

        $this->_log('[' . __FUNCTION__ . ']: '
                . __('The "author", the "description" and the "copyright" of the site have been moved to the collection created for the site.'),
            Zend_Log::INFO);

        $this->_log('[' . __FUNCTION__ . ']: ' . __('The first site has been created.'),
            Zend_Log::INFO);
    }

    protected function _upgradeItemTypes()
    {
        $customItemTypes = $this->_getCustomItemTypes();
        $message = '[' . __FUNCTION__ . ']: '
            . __('In Omeka S, it’s possible to create one or more specific forms for each type of item.')
            . ' ';

        if ($customItemTypes) {
            $list = implode('", "', array_map(function ($v) { return $v->name; }, $customItemTypes));
            $this->_log($message . (count($customItemTypes) <= 1
                    ? __('Like one item type is customized ("%s"), you can check resource templates to recreate it.',
                        $list)
                    : __('Like %s items types are customized ("%s"), you can check resource templates to recreate them.',
                        count($customItemTypes), $list)),
                Zend_Log::INFO);
        }
        // Just an info.
        else {
            $this->_log($message
                . __('So, if you have customized your item types, you can check resource templates.'),
                Zend_Log::INFO);
        }

        $unmappedItemTypes = $this->_getUnmappedItemTypes();
        $message = '[' . __FUNCTION__ . ']: ';
        if ($unmappedItemTypes) {
            $list = implode('", "', array_map(function ($v) {
                return $v->name;
            }, $unmappedItemTypes));
            $this->_log($message . (count($unmappedItemTypes) <= 1
                ? __('One used item type ("%s") is not mapped and won’t be upgraded.',
                    $list)
                : __('%d used item types ("%s") are not mapped and won’t be upgraded.',
                    count($unmappedItemTypes), $list)),
                Zend_Log::WARN);
        }
        // Just an info.
        else {
            $this->_log($message
                . __('All used item types are mapped.'),
                Zend_Log::INFO);
        }

        // TODO Create resource templates for customized item types.
        // TODO The resource templates can contains only the truly used elements.
    }

    protected function _upgradeElements()
    {
        $unmappedElements = $this->_getUnmappedElements();
        $message = '[' . __FUNCTION__ . ']: '
            . __('In Omeka S, it’s not possible currently to create a specific property (element) without a rdf vocabulary or a module.')
            . ' ';

        if ($unmappedElements) {
            $list = implode('", "', array_map(function ($v) {
                $elementSet = $v->getElementSet();
                return ($elementSet ? $elementSet->name : '[' . __('No Element Set') . ']')
                    . ':' . $v->name;
            }, $unmappedElements));
            $this->_log($message . (count($unmappedElements) <= 1
                    ? __('One used element ("%s") is not mapped and won’t be upgraded.',
                        $list)
                    : __('%d used elements ("%s") are not mapped and won’t be upgraded.',
                        count($unmappedElements), $list)),
                Zend_Log::WARN);
        }
        // Just an info.
        else {
            $this->_log($message
                . __('Anyway, all used elements are mapped, so all metadata will be available.'),
            Zend_Log::INFO);
        }

        // TODO Allow to create properties in a custom ontology, or list more than the default ones.
    }

    protected function _upgradeItems()
    {
        // Because items are the first resource upgraded, their ids are kept.
        // This implies that files are upgraded separately and that the
        // collection id of all items are set in a second step.
        $this->_upgradeRecords('Item');

        $this->_log('[' . __FUNCTION__ . ']: ' . __('The record status "Featured" doesn’t exist in Omeka S.'),
            Zend_Log::INFO);
    }

    protected function _createItemSetForSite()
    {
        $db = $this->_db;
        $targetDb = $this->getTargetDb();

        $user = $this->getParam('user');

        $recordType = 'Collection';

        $siteId = $this->_siteId;

        // This should be the first item set to simplify next processes.
        $totalTarget= $this->countTargetTable('item_set');
        if ($totalTarget) {
            throw new UpgradeToOmekaS_Exception(
                __('The item set created for the site should be the first one.'));
        }

        // TODO Add the resource template when it will be created.
        $defaultResourceTemplateId = null;

        $id = null;

        $toInserts = array();

        $toInsert = array();
        $toInsert['id'] = $id;
        $toInsert['owner_id'] = $this->_mappingIds['User'][$user->id];
        $toInsert['resource_class_id'] = null;
        $toInsert['resource_template_id'] = $defaultResourceTemplateId;
        $toInsert['is_public'] = 1;
        $toInsert['created'] = $this->getDatetime();
        $toInsert['modified'] = $this->getDatetime();
        $toInsert['resource_type'] = $this->_mappingRecordClasses[$recordType];
        $toInserts['resource'][] = $this->_dbQuote($toInsert);

        $id = 'LAST_INSERT_ID()';

        $toInsert = array();
        $toInsert['id'] = $id;
        $toInsert['is_open'] = 1;
        $toInserts['item_set'][] = $this->_dbQuote($toInsert, 'id');

        // Give it some metadata (6).
        $properties = array();
        $properties['dcterms:title'][] = __('All items of the site "%s"', $this->getSiteTitle());
        $properties['dcterms:creator'][] = get_option('author') ?: $user->name;
        $properties['dcterms:rights'][] = get_option('copyright') ?: __('Public Domain');
        $properties['dcterms:description'][] = get_option('description') ?: __('This collection contains all items of the site.');
        $properties['dcterms:date'][] = $this->getDatetime();
        $properties['dcterms:replaces'][] = array(
            'type' => 'uri',
            'uri' => WEB_ROOT,
            'value' => __('Digital library powered by Omeka Classic'),
        );
        $toInserts['value'] = $this->_prepareRowsForProperties($id, $properties);

        $this->_insertRowsInTables($toInserts);

        // Save the item set id of the site. This is the first item set.
        $select = $targetDb->select()
            ->from('item_set', array(new Zend_Db_Expr('MAX(id)')));
        $itemSetSiteId = (integer) $targetDb->fetchOne($select);
        if (empty($itemSetSiteId)) {
            throw new UpgradeToOmekaS_Exception(
                __('The first created site can’t be found in Omeka S.'));
        }

        $this->_itemSetSiteId = $itemSetSiteId;

        // Set the item set as the first one for the site.
        $toInserts = array();
        $toInsert = array();
        $toInsert['id'] = null;
        $toInsert['site_id'] = $siteId;
        $toInsert['item_set_id'] = $itemSetSiteId;
        $toInsert['position'] = 1;
        $toInserts[] = $this->_dbQuote($toInsert);
        $this->_insertRows('site_item_set', $toInserts);

        $this->_log('[' . __FUNCTION__ . ']: ' . __('An item set has been set for the site.'),
            Zend_Log::INFO);
    }

    protected function _upgradeCollections()
    {
        $this->_upgradeRecords('Collection');

        $this->_log('[' . __FUNCTION__ . ']: ' . __('The status "Is Open" has been added to item sets.'),
            Zend_Log::INFO);
    }

    protected function _setCollectionsOfItems()
    {
        $recordType = 'Item';
        $mappedType = 'item_item_set';

        $db = $this->_db;
        $targetDb = $this->getTargetDb();

        $totalRecords = total_records($recordType);
        if (empty($totalRecords)) {
            return;
        }

        $siteId = $this->_siteId;

        $table = $db->getTable($recordType);

        // Get the item set id of the site, if any.
        $itemSetSiteId = $this->_itemSetSiteId;

        $loops = floor(($totalRecords - 1) / $this->maxChunk) + 1;
        for ($page = 1; $page <= $loops; $page++) {
            $records = $table->findBy(array(), $this->maxChunk, $page);

            $toInserts = array();
            foreach ($records as $record) {
                if (!empty($record->collection_id)) {
                    if (!isset($this->_mappingIds['Collection'][$record->collection_id])) {
                        throw new UpgradeToOmekaS_Exception(
                            __('The collection #%d for item #%d can’t be found in Omeka S.',
                                $record->collection_id, $record->id)
                            . ' ' . __('Check the processors of the plugins.'));
                    }

                    $toInsert = array();
                    $toInsert['item_id'] = $record->id;
                    $toInsert['item_set_id'] = $this->_mappingIds['Collection'][$record->collection_id];
                    $toInserts[] = $this->_dbQuote($toInsert);
                }

                if ($itemSetSiteId) {
                    $toInsert = array();
                    $toInsert['item_id'] = $record->id;
                    $toInsert['item_set_id'] = $itemSetSiteId;
                    $toInserts[] = $this->_dbQuote($toInsert);
                }
            }

            $this->_insertRows($mappedType, $toInserts);
        }

        // A final check, normally useless.

        // Get the total of items with a collection.
        $select = $db->getTable('Item')->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->from(array(), array(new Zend_Db_Expr('COUNT(*)')))
            ->where('items.collection_id IS NOT NULL');
        $totalItemsWithCollection = $db->fetchOne($select);

        $totalTarget = $this->countTargetTable($mappedType);
        if ($itemSetSiteId) {
            $totalTarget -= $totalRecords;
        }
        if ($totalItemsWithCollection > $totalTarget) {
            throw new UpgradeToOmekaS_Exception(
                __('Only %d/%d %s have been upgraded into "%s".',
                    $totalTarget, $totalItemsWithCollection, __('items with a collection'), $mappedType));
        }

        // May be possible with plugins?
        if ($totalItemsWithCollection < $totalTarget) {
            throw new UpgradeToOmekaS_Exception(
                __('There are %d upgraded "%s" in Omeka S, but only %d %s in Omeka C.',
                    $totalTarget, $mappedType, $totalItemsWithCollection, __('items with a collection'))
                . ' ' . __('Check the processors of the plugins.'));
        }

        $this->_log('[' . __FUNCTION__ . ']: ' . __('In Omeka S, an item can belong to multiple collections (item sets) and multipe sites.'),
            Zend_Log::INFO);
    }

    protected function _upgradeFiles()
    {
        $this->_upgradeRecords('File');

        $this->_log('[' . __FUNCTION__ . ']: ' . __('In Omeka S, each attached files can be hidden/shown separately.'),
            Zend_Log::INFO);
    }

    /**
     * Helper to upgrade standard records of Omeka C (items, collections, files).
     *
     * @param string $recordType
     * @throws UpgradeToOmekaS_Exception
     * @return void
     */
    protected function _upgradeRecords($recordType)
    {
        if (!isset($this->_mappingRecordTypes[$recordType])) {
            return;
        }
        $mappedType = $this->_mappingRecordTypes[$recordType];

        // Prepare a string for the messages.
        $recordTypeSingular = strtolower(Inflector::humanize(Inflector::underscore($recordType)));
        $recordTypePlural = strtolower(Inflector::pluralize($recordTypeSingular));

        $totalRecords = total_records($recordType);
        if (empty($totalRecords)) {
            $this->_log('[' . __FUNCTION__ . ']: ' . __('No %s to upgrade.',
                $recordTypeSingular), Zend_Log::INFO);
            return;
        }

        $siteId = $this->_siteId;

        $db = $this->_db;
        $targetDb = $this->getTargetDb();

        $user = $this->getParam('user');

        // The process uses the regular queries of Omeka in order to keep
        // only good records and to manage filters.
        $table = $db->getTable($recordType);

        // The list of user ids allows to check if the owner of a record exists.
        // The id of users are kept between Omeka C and Omeka S.
        $userIds = $this->_getRecordIds('User');
        // Manage the special case where there is no user.
        if (empty($userIds)) {
            $userIds[$user->id] = $user->id;
        }

        // TODO Add the resource template when it will be created.
        $defaultResourceTemplateId = null;

        $totalItemTypesUnmapped = 0;

        // Specificities for each record type. This avoids to loop some process.
        switch ($recordType) {
            case 'Item':
                // Check if there are already records.
                $totalExisting = $this->countTargetTable('resource');
                if ($totalExisting) {
                    // TODO Allow to upgrade without ids (need the last inserted id and a temp mapping of source and destination ids)?
                    throw new UpgradeToOmekaS_Exception(
                        __('Some items (%d) have been upgraded, so ids won’t be kept.',
                            $totalExisting)
                        . ' ' . __('Check the processors of the plugins.'));
                }

                $mappingItemTypes = $this->getMappingItemTypesToClasses();
                break;

            // Nothing specific for collections and files.
        }

        $loops = floor(($totalRecords - 1) / $this->maxChunk) + 1;
        for ($page = 1; $page <= $loops; $page++) {
            $records = $table->findBy(array(), $this->maxChunk, $page);

            // Initialize the array to map ids of collections and files.
            $remapIds = array();

            $baseId = 0;
            $toInserts = array();
            foreach ($records as $record) {
                $toInsert = array();
                switch ($recordType) {
                    case 'Item':
                        $id = $record->id;
                        $ownerId = isset($userIds[$record->owner_id])
                                && isset($this->_mappingIds['User'][$record->owner_id])
                            ? $this->_mappingIds['User'][$record->owner_id]
                            : null;
                        if (empty($mappingItemTypes[$record->item_type_id])) {
                            $resourceClassId = null;
                        } elseif (isset($mappingItemTypes[$record->item_type_id])) {
                            $resourceClassId = $mappingItemTypes[$record->item_type_id];
                        } else {
                            $resourceClassId = null;
                            ++$totalItemTypesUnmapped;
                        }
                        $isPublic = (integer) (boolean) $record->public;
                        break;
                    case 'Collection':
                        $id = null;
                        $ownerId = isset($userIds[$record->owner_id])
                                && isset($this->_mappingIds['User'][$record->owner_id])
                            ? $this->_mappingIds['User'][$record->owner_id]
                            : null;
                        $resourceClassId = null;
                        $isPublic = (integer) (boolean) $record->public;
                        break;
                    case 'File':
                        $id = null;
                        $item = $record->getItem();
                        $ownerId = isset($userIds[$item->owner_id])
                                && isset($this->_mappingIds['User'][$item->owner_id])
                            ? $this->_mappingIds['User'][$item->owner_id]
                            : null;
                        $resourceClassId = null;
                        $isPublic = 1;
                        break;
                }

                $toInsert['id'] = $id;
                $toInsert['owner_id'] = $ownerId;
                $toInsert['resource_class_id'] = $resourceClassId;
                $toInsert['resource_template_id'] = $defaultResourceTemplateId;
                $toInsert['is_public'] = $isPublic;
                $toInsert['created'] = $record->added;
                $toInsert['modified'] = $record->modified;
                $toInsert['resource_type'] = $this->_mappingRecordClasses[$recordType];
                $toInserts['resource'][] = $this->_dbQuote($toInsert);

                // Check if this is an autoinserted id.
                if (empty($id)) {
                    $remapIds[$recordType][] = $record->id;
                    $id = 'LAST_INSERT_ID() + ' . $baseId;
                    ++$baseId;
                }
                else {
                    $this->_mappingIds[$recordType][$record->id] = $id;
                }

                $toInsert = array();
                $toInsert['id'] = $id;
                switch ($recordType) {
                    case 'Item':
                        // Currently, the table "item" contains only the id.
                        break;
                    case 'Collection':
                        $toInsert['is_open'] = 1;
                        break;
                    case 'File':
                        $source = $record->original_filename;
                        $scheme = parse_url($source, PHP_URL_SCHEME);
                        $isRemote = UpgradeToOmekaS_Common::isRemote($source);
                        $extension = pathinfo($source, PATHINFO_EXTENSION);
                        // This allows to manage the plugin Archive Repertory.
                        $storageId = $extension
                            ? substr($record->filename, 0, strrpos($record->filename, $extension) - 1)
                            : $record->filename;
                        $toInsert['item_id'] = $item->id;
                        $toInsert['ingester'] = $isRemote ? 'url' : 'upload';
                        $toInsert['renderer'] = 'file';
                        $toInsert['data'] = null;
                        $toInsert['source'] = $source;
                        $toInsert['media_type'] = $record->mime_type;
                        $toInsert['storage_id'] = $storageId;
                        $toInsert['extension'] = $extension;
                        // The sha256 is optional and set later (here or in the
                        // compatibility module).
                        $toInsert['sha256'] = null;
                        $toInsert['has_original'] = 1;
                        $toInsert['has_thumbnails'] = (integer) (boolean) $record->has_derivative_image;
                        $toInsert['position'] = $record->order ?: 1;
                        $toInsert['lang'] = null;
                        break;
                }
                $toInserts[$mappedType][] = $this->_dbQuote($toInsert, 'id');
            }

            $this->_insertRowsInTables($toInserts);

            // Remaps only if needed.
            if (!empty($remapIds[$recordType])) {
                $this->_remapIds($remapIds[$recordType], $recordType);
            }
        }

        // A final check, normally useless.
        $totalTarget = $this->countTargetTable($mappedType);

        // Substract the default item set for the site beforechecks, if any.
        if ($recordType == 'Collection' && $this->_itemSetSiteId) {
            --$totalTarget;
        }

        if ($totalRecords > $totalTarget) {
            throw new UpgradeToOmekaS_Exception(
                __('Only %d/%d %s have been upgraded into "%s".',
                    $totalTarget, $totalRecords, $recordTypePlural, $mappedType));
        }

        // May be possible with plugins?
        if ($totalRecords < $totalTarget) {
            throw new UpgradeToOmekaS_Exception(
                __('There are %d upgraded "%s" in Omeka S, but only %d %s in Omeka C.',
                    $totalTarget, $mappedType, $totalRecords, $recordTypePlural)
                . ' ' . __('Check the processors of the plugins.'));
        }

        if (in_array($recordType, array('Item', 'Collection'))) {
            // The roles are checked, because at this point, all users were
            // upgraded according to the role and there is no option to set a
            // default role to the users without an existing role.
            $lostOwners = $this->countRecordsWithoutOwner($recordType, 'owner_id', true);
            if ($lostOwners) {
                $this->_log('[' . __FUNCTION__ . ']: '
                    . ($lostOwners <= 1
                        ? __('One %s has lost its owner.', $recordTypeSingular)
                        : __('%d %s have lost their owner.', $lostOwners, $recordTypePlural)),
                    Zend_Log::NOTICE);
            }

            if ($totalItemTypesUnmapped) {
                $this->_log('[' . __FUNCTION__ . ']: ' . __('The item type of %d items was not mapped and was not upgraded.',
                    $totalItemTypesUnmapped), Zend_Log::WARN);
            }
        }

        $this->_log('[' . __FUNCTION__ . ']: ' . __('All %s (%d) have been upgraded as "%s".',
            $recordTypePlural, $totalRecords, $mappedType), Zend_Log::INFO);
    }

    /**
     * Helper to remap ids for a record type.
     *
     * @internal This is possible, because only one user uses the database.
     *
     * @param array $remapIds
     * @param string $recordType
     * @throws UpgradeToOmekaS_Exception
     */
    protected function _remapIds($remapIds, $recordType)
    {
        $db = $this->_db;
        $targetDb = $this->getTargetDb();

        $mappedType = $this->_mappingRecordTypes[$recordType];

        // Prepare a string for the messages.
        $recordTypeSingular = strtolower(Inflector::humanize(Inflector::underscore($recordType)));
        $recordTypePlural = strtolower(Inflector::pluralize($recordTypeSingular));

        // Initialize the mapping if needed.
        if (empty($this->_mappingIds[$recordType])) {
            $this->_mappingIds[$recordType] = array();
        }

        // Do a precheck on the source ids.
        if (array_intersect(array_keys($this->_mappingIds[$recordType]), $remapIds)) {
            throw new UpgradeToOmekaS_Exception(
                __('Some %s ids are already mapped between source and destination.',
                    $recordTypeSingular));
        }

        // Get the last greatest ids of the record table, in order.
        $max = count($remapIds);
        $sql = '
        (
            SELECT `id`
            FROM ' . $mappedType . '
            ORDER BY `id` DESC
            LIMIT ' . $max . '
        )
        ORDER BY `id` ASC;';
        $result = $targetDb->fetchCol($sql);

        if (empty($result) || count($result) != count($remapIds)) {
            throw new UpgradeToOmekaS_Exception(
                __('Unable to fetch the last %d %s ids in the target database.',
                    count($remapIds), $recordTypePlural));
        }

        // Check if this is really the n last destination ids. They must
        // not be already mapped.
        if (array_intersect($this->_mappingIds[$recordType], $result)) {
            throw new UpgradeToOmekaS_Exception(
                __('Unable to get the last %d %s ids in the target database.',
                    count($remapIds), $recordTypePlural));
        }

        $this->_mappingIds[$recordType] += array_combine($remapIds, $result);
    }

    protected function _upgradeMetadata()
    {
        $recordType = 'ElementText';

        $mappedType = $this->_mappingRecordTypes[$recordType];

        // Prepare a string for the messages.
        $recordTypeSingular = strtolower(Inflector::humanize(Inflector::underscore($recordType)));
        $recordTypePlural = strtolower(Inflector::pluralize($recordTypeSingular));

        $db = $this->_db;
        $targetDb = $this->getTargetDb();

        $totalRecords = total_records($recordType);
        if (empty($totalRecords)) {
            return;
        }

        $siteId = $this->_siteId;

        $table = $db->getTable($recordType);

        $mapping = $this->getMappingElementsToProperties();

        $totalRecordsUnmapped = 0;

        $loops = floor(($totalRecords - 1) / $this->maxChunk) + 1;
        for ($page = 1; $page <= $loops; $page++) {
            $records = $table->findBy(array(), $this->maxChunk, $page);

            $toInserts = array();
            foreach ($records as $record) {
                $etRecordType = $record->record_type;
                $etRecordId = $record->record_id;

                // Check if a mapping is needed (for collections and files).
                if ($etRecordType == 'Item') {
                    $resourceId = $etRecordId;
                }
                // The record id has changed: check it.
                else {
                    if (!isset($this->_mappingIds[$etRecordType][$etRecordId])) {
                        throw new UpgradeToOmekaS_Exception(
                            __('The %s #%d can’t be found in Omeka S.',
                                $etRecordType, $etRecordId));
                    }
                    $resourceId = $this->_mappingIds[$etRecordType][$etRecordId];
                }

                // Check if the element has been mapped to a property.
                if (empty($mapping[$record->element_id])) {
                    ++$totalRecordsUnmapped;
                    continue;
                }

                $toInsert = array();
                $toInsert['id'] = null;
                $toInsert['resource_id'] = $resourceId;
                $toInsert['property_id'] = $mapping[$record->element_id];
                $toInsert['value_resource_id'] = null;
                $toInsert['type'] = 'literal';
                $toInsert['lang'] = null;
                $toInsert['value'] = $record->text;
                $toInsert['uri'] = null;
                $toInserts[] = $this->_dbQuote($toInsert);
            }

            $this->_insertRows('value', $toInserts);
        }

        // A final check, normally useless.
        $totalRecordsMapped = $totalRecords - $totalRecordsUnmapped;
        $totalTarget = $this->countTargetTable($mappedType);
        // Substract the six properties of the default item set for the site.
        if ($this->_itemSetSiteId) {
            $totalTarget -= 6;
        }

        $sql = "SELECT id, resource_id, property_id, value FROM value";
        $result = $targetDb->fetchAll($sql);

        if ($totalRecordsMapped > $totalTarget) {
            throw new UpgradeToOmekaS_Exception(
                __('Only %d/%d %s have been upgraded into "%s".',
                    $totalTarget, $totalRecordsMapped, $recordTypePlural, $mappedType));
        }
        // May be possible with plugins?
        if ($totalRecords < $totalTarget) {
            throw new UpgradeToOmekaS_Exception(
                __('An error occurred: there are %d upgraded "%s" in Omeka S, but only %d %s in Omeka C.',
                    $totalTarget, $mappedType, $totalRecordsMapped, $recordType)
                . ' ' . __('Check the processors of the plugins.'));
        }

        if ($totalRecordsUnmapped) {
            $this->_log('[' . __FUNCTION__ . ']: ' . __('%d metadata with not mapped elements were not upgraded.',
                $totalRecordsUnmapped), Zend_Log::WARN);
            $this->_log('[' . __FUNCTION__ . ']: ' . __('The other %d metadata have been upgraded as "%s".',
                $totalRecordsMapped, $mappedType), Zend_Log::INFO);
        }
        else {
            $this->_log('[' . __FUNCTION__ . ']: ' . __('All %s (%d) have been upgraded as "%s".',
                __('metadata'), $totalRecords, $mappedType), Zend_Log::INFO);
        }
    }

    protected function _copyFiles()
    {
        $recordType = 'File';

        // Check the total of records.
        $totalRecords = total_records($recordType);
        if (empty($totalRecords)) {
            $this->_log('[' . __FUNCTION__ . ']: ' . __('No file to copy.'),
                Zend_Log::DEBUG);
            return;
        }

        // Get and check the type.
        $filesType = $this->getParam('files_type');
        if (!in_array($filesType, array('hard_link', 'copy', 'dummy'))) {
            throw new UpgradeToOmekaS_Exception(
                __('The type "%s" is not supported.', $type));
        }

        // Check the mapping of files.
        $destDir = $this->getParam('base_dir') . DIRECTORY_SEPARATOR . 'files';
        $mapping = $this->getMerged('mapping_files');
        if (empty($mapping)) {
            throw new UpgradeToOmekaS_Exception(
                __('The mapping of files is empty.'));
        }

        // Prepare the mapping with the full paths
        foreach ($mapping as $type => $map) {
            $sourceDir = key($map);
            $destinationDir = reset($map);
            // Check if the path exists and absolute.
            $path = realpath($sourceDir);
            if ($path != $sourceDir) {
                $sourceDir = FILES_DIR . DIRECTORY_SEPARATOR . $sourceDir;
            }
            if (!file_exists($sourceDir) || !is_dir($sourceDir) || !is_readable($sourceDir)) {
                throw new UpgradeToOmekaS_Exception(
                    __('The source directory "%s" is not readable.', $sourceDir));
            }
            $path = realpath($destinationDir);
            if ($path != $destinationDir) {
                $destinationDir = $destDir . DIRECTORY_SEPARATOR . $destinationDir;
            }
            // The destination dirs are not included in the source, but created
            // dynamically. That what is done here.
            $result = UpgradeToOmekaS_Common::createDir($destinationDir);
            if (!$result) {
                throw new UpgradeToOmekaS_Exception(
                    __('The destination directory "%s" is not writable.', $destinationDir));
            }
            $mapping[$type] = array($sourceDir => $destinationDir);
        }

        $db = $this->_db;
        $table = $db->getTable($recordType);

        $path = key($mapping['original']);
        $totalFiles = UpgradeToOmekaS_Common::countFilesInDir($path);

        $totalCopied = 0;

        // Copy only the files that are referenced inside the database.
        $loops = floor(($totalRecords - 1) / $this->maxChunk) + 1;
        for ($page = 1; $page <= $loops; $page++) {
            $records = $table->findBy(array(), $this->maxChunk, $page);
            foreach ($records as $record) {
                foreach ($mapping as $type => $map) {
                    // Original is an exception: the extension is the original
                    // one and may  be in uppercase or not.
                    $filename = $type == 'original'
                        ? $record->filename
                        : $record->getDerivativeFilename($type);
                    $sourceDir = key($map);
                    $destinationDir = reset($map);
                    $source = $sourceDir . DIRECTORY_SEPARATOR . $filename;
                    $destination = $destinationDir . DIRECTORY_SEPARATOR . $filename;

                    // A check is done to manage the plugin Archive Repertory,
                    // that creates a relative dir for each record.
                    if (strpos($filename, DIRECTORY_SEPARATOR) !== false) {
                        $result = UpgradeToOmekaS_Common::createDir(dirname($destination));
                        if (!$result) {
                            throw new UpgradeToOmekaS_Exception(
                                __('Unable to create the directory "%s".', dirname($destination)));
                        }
                    }

                    switch ($filesType) {
                        case 'hard link':
                            break;
                        case 'copy':
                            $result = copy($source, $destination);
                            break;
                        case 'dummy':
                            break;
                    }
                    if (!$result) {
                        throw new UpgradeToOmekaS_Exception(
                            __('The copy of the file "%s" to the directory "%s" failed.',
                                $source, dirname($destination)));
                    }
                }
                // Count only one copy by record.
                ++$totalCopied;
            }
        }

        if ($totalCopied != $totalFiles) {
            $this->_log('[' . __FUNCTION__ . ']: ' . __('All %d files of records have been copied (%s) into Omeka S, but there are %d files in Omeka C.',
                $totalCopied, $filesType, $totalFiles), Zend_Log::WARN);
        }
        // Fine.
        else {
            $this->_log('[' . __FUNCTION__ . ']: ' . __('All %d files have been copied (%s) into Omeka S.',
                $totalCopied, $filesType), Zend_Log::INFO);
        }
    }

    protected function _copyThemes()
    {
        // with theme media uploaded.
    }

    protected function _downloadCompatibilityModule()
    {
        // TODO Compatibility module.
    }

    protected function _unzipCompatibiltyModule()
    {
        // TODO Compatibility module.
    }

    protected function _installCompatibiltyModule()
    {
        // TODO Compatibility module.
    }

    /**
     * Helper to convert an array into an indented raw file.
     *
     * @internal This quick and dirty tool is designed only for local.config.php.
     *
     * @param array $nestedArray
     * @return string
     */
    private function _createRawArray(array $nestedArray)
    {
        $indent = '    ';

        $output = array();
        $output[] = '<?php';
        $output[] = 'return [';

        function nestOutput($output, $array, $depth = 0, $indent = '    ') {
            $indentString = str_repeat($indent, $depth);
            foreach ($array as $key => $value) {
                // Manage an exception: no quote for a string.
                if ($key == 'priority') {
                    $output[] = $indentString . "'" . $key . "' => " . $value . ',';
                }
                elseif (is_array($value)) {
                    if (count($value) == 0) {
                        $output[] = $indentString . "'" . $key . "' => [],";
                    } elseif (count($value) == 1 && $key == 'constraint') {
                        $v = reset($value);
                        $output[] = $indentString . "'" . $key . "' => ['" . key($value) . "' => " . printValue($v) . '],';
                    } else {
                        $output[] = $indentString . "'" . $key . "' => [";
                        $output = nestOutput($output, $value, ($depth + 1), $indent);
                        $output[] = $indentString . '],';
                    }
                }
                else {
                    $output[] = $indentString . "'" . $key . "' => " . printValue($value) . ',';
                }
            }
            return $output;
        }

        function printValue($value) {
            $type = gettype($value);
            switch ($type) {
                case 'NULL':
                    return 'null';
                case 'boolean':
                    return $value ? 'true' : 'false';
                case 'integer':
                    return $value;
                case 'string':
                    return "'" . str_replace ("'", "\\'", $value) . "'";
                default:
                    return (string) $value;
            }
        }

        $output = nestOutput($output, $nestedArray, 1, $indent);

        $output[] = '];';
        $result = implode(PHP_EOL, $output) . PHP_EOL;
        return $result;
    }

    protected function _convertNavigation()
    {
        // From public_nav_main()
        $nav = new Omeka_Navigation;
        $nav->loadAsOption(Omeka_Navigation::PUBLIC_NAVIGATION_MAIN_OPTION_NAME);
        $nav->addPagesFromFilter(Omeka_Navigation::PUBLIC_NAVIGATION_MAIN_FILTER_NAME);
        // Process nav directly.
        $nav = $nav->toArray();
        foreach ($nav as &$page) {
            $page['visible'] ? $this->_convertNavigationPage($page) : $page = null;
        }

        $nav = array_filter($nav);
        return $nav;
    }

    protected function _convertNavigationPage(&$page)
    {
        if (is_array($page)) {
            $result = $this->_convertNavigationPageValues($page);
            if (isset($page['pages'])) {
                foreach ($page['pages'] as &$subpage) {
                    $subpage['visible'] ? $this->_convertNavigationPage($subpage) : $subpage = null;
                }
                $result['links'] = array_filter($page['pages']);
            } else {
                $result['links'] = array();
            }
            $page = $result;
        }
        // Else single value: no change and no return.
    }

    protected function _convertNavigationPageValues($page)
    {
        static $baseRoot;
        static $omekaPath;
        static $omekaSPath;
        static $omekaSSitePath;

        if (is_null($baseRoot)) {
            $parsed = parse_url(WEB_ROOT);
            $baseRoot = $parsed['scheme'] . '://' . $parsed['host'] . (!empty($parsed['port']) ? ':' . $parsed['port'] : '');
            $omekaPath = substr(WEB_ROOT, strlen($baseRoot));
            $omekaSPath = substr($this->getParam('url'), strlen($baseRoot));
            $omekaSSitePath = $omekaSPath . '/s/' . $this->getSiteSlug();
        }

        // The uri doesn't keep the fragment?
        $url = isset($page['uri']) ? $page['uri'] : $page['uid'];

        if ($url == WEB_ROOT) {
            return array(
                'type' => 'url',
                'data' => array(
                    'label' => $page['label'],
                    'url' => $this->getParam('url'),
            ));
        }

        $parsed = parse_url($url);
        $isRemote = isset($parsed['scheme']) && in_array($parsed['scheme'], array('http', 'https'));
        // Check if this is an external url.
        if ($isRemote && strpos($url, WEB_ROOT) !== 0) {
            return array(
                'type' => 'url',
                'data' => array(
                    'label' => $page['label'],
                    'url' => $url,
            ));
        }

        // Get the path without the Omeka 2 path, if any.
        $path = substr($parsed['path'], strlen($omekaPath));
        $parsed['url'] = $url;
        $parsed['fullpath'] = $parsed['path'];
        $parsed['path'] = $path;

        $processors = $this->getProcessors();
        foreach ($processors as $processor) {
            $result = $processor->convertNavigationPageToLink(
                $page,
                $parsed,
                array(
                    'baseRoot' => $baseRoot,
                    'omekaPath' => $omekaPath,
                    'omekaSPath' => $omekaSPath,
                    'omekaSSitePath' => $omekaSSitePath,
            ));
            if ($result) {
                return $result;
            }
        }

        // Not found: return the full url.
        return array(
            'type' => 'url',
            'data' => array(
                'label' => $page['label'],
                'url' => $url,
        ));
    }

    public function convertNavigationPageToLink($page, $parsed, $site)
    {
        $omekaSPath = $site['omekaSPath'];
        $omekaSSitePath = $site['omekaSSitePath'];
        $path = $parsed['path'];
        switch ($path) {
            case '/search':
            case '/items/search':
                return array(
                    'type' => 'url',
                    'data' => array(
                        'label' => $page['label'],
                        'url' => $omekaSSitePath . '/item/search',
                        // TODO Convert the query if any.
                        // 'query' => '',
                ));
            case '/items':
            case '/items/browse':
                return array(
                    'type' => 'browse',
                    'data' => array(
                        'label' => $page['label'],
                        // TODO Convert the query if any.
                        'query' => '',
                ));
            case '/collections':
            case '/collections/browse':
                return array(
                    'type' => 'browseItemSets',
                    'data' => array(
                        'label' => $page['label'],
                        // TODO Convert the query if any.
                        'query' => '',
                ));
            case strpos($path, '/items/show/') === 0:
                // Id of items are kept.
                $id = substr($path, strlen('/items/show/'));
                return array(
                    'type' => 'url',
                    'data' => array(
                        'label' => $page['label'],
                        'url' => $omekaSSitePath . '/item/' . $id,
                ));
            case strpos($path, '/collections/show/') === 0:
                // TODO Wrong path to collection (id changes).
                $id = substr($path, strlen('/collections/show/'));
                return array(
                    'type' => 'url',
                    'data' => array(
                        'label' => $page['label'],
                        'url' => $omekaSSitePath . '/item-set/' . $id,
                ));
            case strpos($path, '/files/show/') === 0:
                // TODO Wrong path to file (id changes).
                $id = substr($path, strlen('/files/show/'));
                return array(
                    'type' => 'url',
                    'data' => array(
                        'label' => $page['label'],
                        'url' => $omekaSSitePath . '/media/' . $id,
                ));
            case '/users':
            case '/users/login':
                return array(
                    'type' => 'url',
                    'data' => array(
                        'label' => $page['label'],
                        'url' => $omekaSPath . '/login',
                ));
            case '/users/logout':
                return array(
                    'type' => 'url',
                    'data' => array(
                        'label' => $page['label'],
                        'url' => $omekaSPath . '/logout',
                ));
            case '/map':
            case '/geolocation':
            case '/geolocation/map':
            case '/geolocation/map/browse':
                return array(
                    'type' => 'mapping',
                    'data' => array(
                        'label' => $page['label'],
                ));
            case '/exhibits':
            case '/exhibits/browse':
                return array(
                    'type' => 'url',
                    'data' => array(
                        'label' => $page['label'],
                        'url' => $omekaSPath,
                ));
            case strpos($path, '/exhibits/show/') === 0:
                $slug = substr($path, strlen('/exhibits/show/'));
                $pos = strpos($slug, '/');
                if ($pos === false) {
                    // The exhibit exists, because it's a visible internal link.
                    return array(
                        'type' => 'url',
                        'data' => array(
                            'label' => $page['label'],
                            // TODO The slug shouldn't be the same than the site (very rare).
                            'url' => $omekaSPath . '/s/' . $slug,
                    ));
                }
                // TODO Get the id of the page (the number of simple pages + the id of the exhibit page)..
                $id = 1;
                return array(
                    'type' => 'page',
                    'data' => array(
                        'label' => $page['label'],
                        'id' => $id,
                ));
        }
    }

    /**
     * Get all visible or invisible pages.
     *
     * @todo Check if a visible page is under an invisible page.
     *
     * @param string $visible
     * @return unknown
     */
    protected function _countNavigationPages($visible = true)
    {
        // From public_nav_main()
        $nav = new Omeka_Navigation;
        $nav->loadAsOption(Omeka_Navigation::PUBLIC_NAVIGATION_MAIN_OPTION_NAME);
        $nav->addPagesFromFilter(Omeka_Navigation::PUBLIC_NAVIGATION_MAIN_FILTER_NAME);
        $total = $nav->findAllBy('visible', $visible);
        return count($total);
    }

    /**
     * Get an array containing all used item types with total.
     *
     * @return array.
     */
    public function getUsedItemTypes()
    {
        $db = $this->_db;
        $sql = "
        SELECT item_types.`id` AS item_type_id,
            item_types.`name` AS item_type_name,
            COUNT(items.`id`) AS total_items
        FROM {$db->ItemType} item_types
        JOIN {$db->Item} items
            ON items.`item_type_id` = item_types.`id`
        GROUP BY item_types.`id`
        ORDER BY item_types.`name`
        ;";
        $itemTypes = $db->fetchAll($sql);
        return $itemTypes;
    }

    /**
     * Get the customized item types (different from the standard ones).
     *
     * @todo Count the total of customized item types too (changed order, etc.).
     * @todo Check the specific element sets too.
     * @todo Manage the item types of the plugins too.
     *
     * @return array
     */
    protected function _getCustomItemTypes()
    {
        $itemTypes = get_records('ItemType', array(), 0);
        $defaultItemTypes = $this->mapping_item_types;

        $result = array();
        foreach ($itemTypes as $itemType) {
            if (!isset($defaultItemTypes[$itemType->name])) {
                $result[$itemType->id] = $itemType;
            }
        }
        return $result;
    }

    /**
     * Get an array containing all used elements with total by record type.
     *
     * @return array.
     */
    public function getUsedElements()
    {
        $db = $this->_db;
        $sql = "
        SELECT element_sets.`id` AS element_set_id,
            element_sets.`name` AS element_set_name,
            elements.`id` AS element_id,
            elements.`name` AS element_name,
            COUNT(collections.`id`) AS total_collections,
            COUNT(items.`id`) AS total_items,
            COUNT(files.`id`) AS total_files
        FROM {$db->ElementSet} element_sets
        JOIN {$db->Element} elements
            ON elements.`element_set_id` = element_sets.`id`
        JOIN {$db->ElementText} element_texts
            ON element_texts.`element_id` = elements.`id`
        LEFT JOIN {$db->Collection} collections
            ON collections.`id` = element_texts.`record_id`
                AND element_texts.`record_type` = 'Collection'
        LEFT JOIN {$db->Item} items
            ON items.`id` = element_texts.`record_id`
                AND element_texts.`record_type` = 'Item'
        LEFT JOIN {$db->File} files
            ON files.`id` = element_texts.`record_id`
                AND element_texts.`record_type` = 'File'
        GROUP BY elements.`id`
        ORDER BY element_sets.`name`, elements.`name`
        ;";
        $elements = $db->fetchAll($sql);
        return $elements;
    }

    /**
     * Get an array containing all item types by used element.
     *
     * @return array.
     */
    public function getItemTypesByUsedElement()
    {
        $db = $this->_db;
        $sql = "
        SELECT
            elements.`id` AS element_id,
            elements.`name` AS element_name,
            item_types.`id` AS item_type_id,
            item_types.`name` AS item_type_name,
            COUNT(items.`id`) AS total
        FROM {$db->Item} items
        JOIN {$db->ElementText} element_texts
            ON element_texts.`record_id` = items.`id`
                AND element_texts.`record_type` = 'Item'
        JOIN {$db->ItemTypesElement} item_types_elements
            ON item_types_elements.`element_id` = element_texts.`element_id`
        JOIN {$db->ItemType} item_types
            ON item_types_elements.`item_type_id` = item_types.`id`
                AND item_types.`id` = items.`item_type_id`
        JOIN {$db->Element} elements
            ON elements.`id` = element_texts.`element_id`
                AND elements.`id` = item_types_elements.`element_id`
        GROUP BY elements.`id`, item_types.`id`
        ORDER BY elements.`name`, item_types.`name`
        ;";
        $elements = $db->fetchAll($sql);
        $result = array();
        foreach ($elements as $element) {
            $result[$element['element_id']][$element['item_type_name']] = $element;
        }
        return $result;
    }

    /**
     * Get the unmapped item types.
     *
     * @return array
     */
    protected function _getUnmappedItemTypes()
    {
        $mapping = $this->getParam('mapping_item_types');
        $unmapped = array_filter($mapping, function ($v) {
            return empty($v);
        });
        $records = get_records('ItemType', array(), 0);
        foreach ($records as $record) {
            if (isset($unmapped[$record->id])) {
                $unmapped[$record->id] = $record;
            }
        }
        return $unmapped;
    }

    /**
     * Get the unmapped elements.
     *
     * @return array
     */
    protected function _getUnmappedElements()
    {
        $mapping = $this->getParam('mapping_elements');
        $unmapped = array_filter($mapping, function ($v) {
            return empty($v);
        });
        $records = get_records('Element', array(), 0);
        foreach ($records as $record) {
            if (isset($unmapped[$record->id])) {
                $unmapped[$record->id] = $record;
            }
        }
        return $unmapped;
    }

    /**
     * Helper to get the list of ids of a record type.
     *
     * @param string $recordType
     * @return array
     */
    protected function _getRecordIds($recordType)
    {
        $db = $this->_db;
        $table = $db->getTable($recordType);
        $alias = $table->getTableAlias();
        $select = $table->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->from(array(), array(
                'id' => $alias  . '.id',
            ))
            ->order($alias . '.id');
        $result = $db->fetchCol($select);
        return array_combine($result, $result);
    }

    /**
     * Helper to get the count of lost owners for a record type.
     *
     * The user may be removed or not upgradable (no role).
     *
     * @param string $recordType
     * @param string $columnName
     * @param boolean $checkRoles Check only for records whose owner has an
     * role upgradable in Omeka S.
     * @return integer
     */
    public function countRecordsWithoutOwner($recordType, $columnName = 'owner_id', $checkRoles = false)
    {
        $db = $this->_db;

        $sqlRoles = '';
        if ($checkRoles) {
            $mappingRoles = $this->getMerged('mapping_roles');
            $roles = array_keys($mappingRoles);
            if ($roles) {
                $sqlRoles = 'OR users.`role` NOT IN ("' . implode('", "', $roles) . '")';
            }
        }

        // Mysql doesn't support full outer join.
        $sql = "
        SELECT
            COUNT(records.`id`) AS total
        FROM {$db->$recordType} records
        LEFT JOIN {$db->User} users
            ON records.`$columnName` = users.`id`
        WHERE records.`$columnName` IS NULL
            OR users.`id` IS NULL
            $sqlRoles
        ;";
        $result = $db->fetchOne($sql);
        return $result;
    }

    /**
     * Helper to get the count of lost owners for a record type in the target.
     *
     * @param string $tableName
     * @param string $columnName
     * @return integer
     */
    public function countTargetRecordsWithoutOwner($tableName, $columnName = 'owner_id')
    {
        // Because there are constraints in the database of Omeka S, a simple
        //check on null is enough.
        $targetDb = $this->getTargetDb();
        $sql = "
        SELECT
            COUNT(record.`id`) AS total
        FROM {$tableName} record
        WHERE record.`$columnName` IS NULL
        ;";
        $result = $targetDb->fetchOne($sql);
        return $result;
    }

    /**
     * Prepare properties for a resource.
     *
     * @param integer|string $resourceId A number or equivalent sql expression.
     * @param unknown $properties
     * @return void
     */
    protected function _prepareRowsForProperties($resourceId, $properties)
    {
        // Get the flat list of properties to get the id of each property.
        $propertiesIds = $this->getPropertyIds();

        // Set the default values for the "value", that is a literal.
        // As all other insertions, keep order and completion of the list.
        $toInsertBase = array();
        $toInsertBase['id'] = null;
        $toInsertBase['resource_id'] = $resourceId;
        $toInsertBase['property_id'] = null;
        $toInsertBase['value_resource_id'] = null;
        $toInsertBase['type'] = 'literal';
        $toInsertBase['lang'] = null;
        $toInsertBase['value'] = null;
        $toInsertBase['uri'] = null;

        $toInserts = array();
        foreach ($properties as $property => $values) {
            if (!isset($propertiesIds[$property])) {
                throw new UpgradeToOmekaS_Exception(
                    __('The property "%s" does not exist in Omeka S.', $property));
            }
            $toInsertBase['property_id'] = $propertiesIds[$property];
            foreach ($values as $value) {
                // This is not a literal value.
                if (is_array($value)) {
                    $toInsert = array_merge($toInsertBase, $value);
                }
                // This is a litteral.
                else {
                    $toInsert = $toInsertBase;
                    $toInsert['value'] = $value;
                }
                $toInserts[] = $this->_dbQuote($toInsert, 'resource_id');
            }
        }

        return $toInserts;
    }

    /**
     * Helper to insert properties for a resource.
     *
     * @uses self::_prepareRowsForProperties()
     * @uses self::_insertRows()
     *
     * @param integer|string $resourceId A number or equivalent sql expression.
     * @param unknown $properties
     * @return void
     */
    protected function _insertProperties($resourceId, $properties)
    {
        $toInserts = $this->_prepareRowsForProperties($resourceId, $properties);
        $this->_insertRows('value', $toInserts);
    }
}
