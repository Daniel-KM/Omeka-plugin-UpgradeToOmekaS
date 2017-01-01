<?php

/**
 * Define methods that should have upgrade classes.
 *
 * @package UpgradeToOmekaS
 */
abstract class UpgradeToOmekaS_Processor_Abstract
{

    /**
     * The name of the plugin.
     *
     * @var string
     */
    public $pluginName;

    /**
     * Minimum version of the plugin managed by the processor.
     *
     * @var string
     */
    public $minVersion = '0';

    /**
     * Maximum version of the plugin managed by the processor.
     *
     * @var string
     */
    public $maxVersion = '0';

    /**
     * Infos about the module for Omeka S, if any.
     *
     * @var array
     */
    public $module = array();

    /**
     * List of methods to process for the upgrade.
     *
     * @var array
     */
    public $processMethods = array();

    /**
     * List of roles mapped from Omeka C to Omeka S.
     *
     * @var array
     */
    public $mappingRoles = array();

    /**
     * Maximum rows to process by loop.
     *
     * @var integer
     */
    public $maxChunk = 100;

    /**
     * The full dir where Omeka Semantic will be installed.
     *
     * @var string
     */
    protected $_baseDir;

    /**
     * Short to the database of Omeka Classic.
     *
     * @var object
     */
    protected $_db;

    /**
     * Short to the database of Omeka Semantic.
     *
     * Even if the database is shared, this is not an alias of $_db since it has
     * a direct access to the database without the Zend layers of Omeka Classic.
     *
     * @var object
     */
    protected $_targetDb;

    /**
     * Short to the ini reader.
     *
     * @var object
     */
    protected $_iniReader;

    /**
     * Short to the security.ini.
     *
     * @var Zend_Ini
     */
    protected $_securityIni;

    /**
     * List of processors.
     *
     * @var array
     */
    protected $_processors = array();

    /**
     * List of parameters.
     *
     * @var array
     */
    protected $_params = array();

    /**
     * Contains the result of prechecks.
     *
     * @var array
     */
    protected $_prechecks = array();

    /**
     * Contains the result of checks.
     *
     * @var array
     */
    protected $_checks = array();

    /**
     * Define is the process is the real one.
     *
     * @var boolean
     */
    protected $_isProcessing;

    /**
     * Single datetime for whole process.
     *
     * @var string
     */
    protected $_datetime;

    /**
     * The slug of the first site on Omeka S.
     *
     * @var string
     */
    protected $_siteSlug;

    /**
     * List of merged roles mapped from Omeka C to Omeka S.
     *
     * @var array
     */
    protected $_mergedMappingRoles;

    /**
     * Constructor of the class.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_db = get_db();
        $this->_iniReader = new Omeka_Plugin_Ini(PLUGIN_DIR);

        // Check if each method exists.
        if ($this->processMethods) {
            foreach ($this->processMethods as $method) {
                if (!method_exists($this, $method)) {
                    throw new UpgradeToOmekaS_Exception(
                        __('The method "%s" of the plugin %s does not exist.', $method, $this->pluginName));
                }
            }
        }
    }

    /**
     * Set the params.
     *
     * @param array $params
     */
    public function setParams(array $params)
    {
        // The right trim of the base path should be trimmed, even when the form
        // is not used.
        if (isset($params['base_dir'])) {
            $params['base_dir'] = rtrim(trim($params['base_dir']), "/\ ");
        }
        $this->_params = $params;
    }

    /**
     * Get the params.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Get a param.
     *
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function setParam($name, $value)
    {
        $this->_params[$name] = $value;
    }

    /**
     * Get a param.
     *
     * @param string $name
     * @return mixed
     */
    public function getParam($name)
    {
        return isset($this->_params[$name]) ? $this->_params[$name] : null;
    }

    /**
     * Get the list of all active processors.
     *
     * @internal Not very clean to set processors inside a processor, even if
     * there is no impact on memory. Used to get filtered values, in particular
     * the list of roles, and to convert the navigation menu.
     *
     * @return array
     */
    public function getProcessors()
    {
        if (empty($this->_processors)) {
            $processors = array();
            $allProcessors = apply_filters('upgrade_omekas', array());

            // Get installed plugins, includes active and inactive.
            $pluginLoader = Zend_Registry::get('pluginloader');
            $installedPlugins = $pluginLoader->getPlugins();

            // Keep only the name of plugins.
            $activePlugins = array_map(function ($v) {
                return $v->isActive() ? $v->name : null;
            }, $installedPlugins);
            $activePlugins = array_filter($activePlugins);
            $activePlugins[] = 'Core';

            // Check processors to prevents possible issues with external plugins.
            foreach ($allProcessors as $name => $class) {
                if (class_exists($class)) {
                    if (is_subclass_of($class, 'UpgradeToOmekaS_Processor_Abstract')) {
                        if (in_array($name, $activePlugins)) {
                            $processor = new $class();
                            $result = $processor->isPluginReady()
                                && !($processor->precheckProcessorPlugin());
                            if ($result) {
                                $processors[$name] = $processor;
                            }
                        }
                    }
                }
            }
            $this->_processors = $processors;
        }
        return $this->_processors;
    }

    /**
     * Set the datetime.
     *
     * @param string $datetime
     */
    public function setDatetime($datetime)
    {
        $this->_datetime = $datetime;
    }

    /**
     * Get the datetime.
     *
     * @return string
     */
    public function getDatetime()
    {
        if (is_null($this->_datetime)) {
            $this->setDatetime(date('Y-m-d H:i:s'));
        }
        return $this->_datetime;
    }

    /**
     * Helper to get the site slug.
     *
     * @return string
     */
    public function getSiteSlug()
    {
        if (empty($this->_siteSlug)) {
            $title = get_option('site_title') ?: __('Site %s', WEB_ROOT);
            $slug = substr($this->_slugify($title), 0, 190);
            $this->_siteSlug = $slug;
            $this->setParam('siteSlug', $slug);
        }
        return $this->_siteSlug;
    }

    /**
     * Get the mapping of roles of all plugins.
     *
     * @return array
     */
    public function getMappingRoles()
    {
        if (is_null($this->_mergedMappingRoles)) {
            $this->_mergedMappingRoles = array();
            $processors = $this->getProcessors();
            foreach ($processors as $processor) {
                $this->_mergedMappingRoles = array_merge(
                    $this->_mergedMappingRoles,
                    $processor->mappingRoles);
            }
        }
        return $this->_mergedMappingRoles;
    }

    /**
     * Get security.ini of the plugin.
     *
     * @return Zend_Config_Ini
     */
    protected function _getSecurityIni()
    {
        if (is_null($this->_securityIni)) {
            $iniFile = dirname(dirname(dirname(dirname(__FILE__))))
            . DIRECTORY_SEPARATOR . 'security.ini';
            $this->_securityIni = new Zend_Config_Ini($iniFile, 'upgrade-to-omeka-s');
        }
        return $this->_securityIni;
    }

    /**
     * Check if the plugin is installed.
     *
     * @return boolean
     */
    public function isPluginReady()
    {
        if (empty($this->pluginName)) {
            return false;
        }
        $plugin = get_record('Plugin', array('name' => $this->pluginName));
        return $plugin && $plugin->isActive();
    }

    /**
     * Precheck if the processor matches the plugin, even not installed.
     *
     * @return string|null Null means no error.
     */
    final public function precheckProcessorPlugin()
    {
        if ($this->pluginName == 'Core') {
            return;
        }

        if (empty($this->pluginName)) {
            return __('The processor of a plugin should have a plugin name, %s hasn’t.', get_class($this));
        }

        // There is a plugin name, so check versions.
        $path = $this->pluginName;
        try {
            $version = $this->_iniReader->getPluginIniValue($path, 'version');
        } catch (Exception $e) {
            return __('The plugin.ini file of the plugin "%s" is not readable: %s',
                $this->pluginName, $e->getMessage());
        }

        if ($version) {
            if ($this->minVersion) {
                if (version_compare($this->minVersion, $version, '>')) {
                    return __('The processor for %s requires a version between %s and %s (current is %s).',
                        $this->pluginName, $this->minVersion, $this->maxVersion, $version);
                }
            }

            if ($this->maxVersion) {
                if (version_compare($this->maxVersion, $version, '<')) {
                    return __('The processor for %s requires a version between %s and %s (current is %s).',
                        $this->pluginName, $this->minVersion, $this->maxVersion, $version);
                }
            }
        }
    }

    /**
     * Quick precheck of the configuration (to display before form, not via a
     * background job).
     *
     * @return array
     */
    final public function precheckConfig()
    {
        if ($this->isPluginReady()) {
            $result = $this->precheckProcessorPlugin();
            if ($result) {
                $this->_prechecks[] = $result;
            }
            // The processor is fine for the plugin.
            else {
                $this->_precheckConfig();
                $this->_precheckIntegrity();
            }
        }
        //  Not installed or disabled.
        else {
            $this->_prechecks[] = __('The plugin is not installed or not active.');
        }

        return $this->_prechecks;
    }

    /**
     * Specific precheck of the config.
     *
     * @return void
     */
    protected function _precheckConfig()
    {
    }

    /**
     * Specific precheck of the integrity of the base and the files.
     *
     * @return void
     */
    protected function _precheckIntegrity()
    {
    }

    /**
     * Quick check of the config with params, mainly for the core.
     *
     * @return array
     */
    final public function checkConfig()
    {
        if ($this->isPluginReady()) {
            $this->_checkConfig();
        }

        return $this->_checks;
    }

    /**
     * Specific quick check of the config with params, mainly for the core.
     *
     * @return void
     */
    protected function _checkConfig()
    {
    }

    /**
     * Set if the process is real.
     *
     * @todo Replace by the check of the main process or by isProcessing().
     *
     * @param boolean
     */
    public function setIsProcessing($value)
    {
        $this->_isProcessing = (boolean) $value;
    }

    /**
     * Set the process id.
     *
     * @param Process|integer $process
     */
    public function setProcessId($process)
    {
        $this->_processId = is_object($process)
            ? (integer) $process->id
            : (integer) $process;
    }

    /**
     * Process the true import.
     *
     * @todo Move this in the job processor.
     *
     * @throws UpgradeToOmekaS_Exception
     * @return null|string Null if no error, else the last message of error.
     */
    final public function process()
    {
        if (!$this->isPluginReady()) {
            return;
        }

        $this->_log(__('Start processing.'), Zend_Log::INFO);

        // The default methods are checked during the construction, but other
        // ones may be added because the list is public.
        $totalMethods = count($this->processMethods);
        foreach ($this->processMethods as $i => $method) {
            $baseMessage = '[' . $method . ']: ';
            // Process stopped externally.
            if (!$this->_isProcessing()) {
                $this->_log($baseMessage . __('The process has been stopped outside of the processor.'),
                    Zend_Log::WARN);
                return;
            }

            // Missing method.
            if (!method_exists($this, $method)) {
                throw new UpgradeToOmekaS_Exception(
                    $baseMessage . __('Method "%s" does not exist.', $method));
            }

            $this->_log($baseMessage . __('Started.'), Zend_Log::INFO);
            try {
                $result = $this->$method();
                // Needed for prechecks and checks.
                if ($result) {
                    throw new UpgradeToOmekaS_Exception($result);
                }
            } catch (Exception $e) {
                throw new UpgradeToOmekaS_Exception($baseMessage . $e->getMessage());
            }
            $this->_log($baseMessage . __('Ended.'), Zend_Log::INFO);
        }

        $this->_log(__('End processing.'), Zend_Log::INFO);
    }

    /**
     * Helper to get the Omeka S database object.
     *
     * @throws UpgradeToOmekaS_Exception
     * @return Db|null
     */
    public function getTargetDb()
    {
        if (!empty($this->_targetDb)) {
            return $this->_targetDb;
        }

        $params = $this->getParams();
        if (empty($params)) {
            throw new UpgradeToOmekaS_Exception(
                __('The params of the processor are not defined.'));
        }

        $type = $this->getParam('database_type');
        switch ($type) {
            case 'separate':
                $host = $this->getParam('database_host');
                $port = $this->getParam('database_port');
                $dbname = $this->getParam('database_name');
                $username = $this->getParam('database_username');
                $password = $this->getParam('database_password');
                break;
            // The default connection can't be reused, because there are the
            // application layers.
            case 'share':
                $db = $this->_db;
                $config = $db->getAdapter()->getConfig();
                $host = isset($config['host']) ? $config['host'] : '';
                $port = isset($config['port']) ? $config['port'] : '';
                $dbname = isset($config['dbname']) ? $config['dbname'] : '';
                $username = isset($config['username']) ? $config['username'] : '';
                $password = isset($config['password']) ? $config['password'] : '';
                break;
            default:
                throw new UpgradeToOmekaS_Exception(
                    __('The type "%s" is not possible for the database.', $type));
        }

        // Check the connection.
        $params = array(
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
                throw new UpgradeToOmekaS_Exception();
            }
        } catch (Exception $e) {
            throw new UpgradeToOmekaS_Exception(
                __('Cannot access to the database "%s".', $dbname));
        }

        $this->_targetDb = $targetDb;
        return $this->_targetDb;
    }

    /**
     * Helper to get an absolute path to a file or a directory inside Omeka S.
     *
     * @param string $path A relative path.
     * @param boolean $check Check if exists and is readable.
     * @return string
     */
    public function getFullPath($path, $check = true)
    {
        $baseDir = $this->getParam('base_dir');
        if (empty($baseDir)) {
            throw new UpgradeToOmekaS_Exception(
                __('Base dir undefined.'));
        }
        $file = $baseDir . DIRECTORY_SEPARATOR . ltrim($path, '/');
        if ($check) {
            if (!file_exists($file)) {
                throw new UpgradeToOmekaS_Exception(
                    __('The file "%s" doesn’t exist.', $path));
            }
            if (!is_readable($file)) {
                throw new UpgradeToOmekaS_Exception(
                    __('The file "%s" is not readable.', $path));
            }
        }
        return $file;
    }

    /**
     * Helper to convert a navigation link from the Omeka 2 to Omeka S.
     *
     * @param array $page The page to convert.
     * @param array $args The url and parsed elements.
     * @param array $site Some data for the url of the site.
     * @return array|null The Omeka S formatted nav link, or null.
     */
    public function convertNavigationPageToLink($page, $args, $site)
    {
    }

    /**
     * Return the status of the process.
     *
     * @todo Uses the status of the process object.
     *
     * @return boolean
     */
    protected function _isProcessing()
    {
        $status = get_option('upgrade_to_omeka_s_process_status');
        return in_array($status, array(
            Process::STATUS_STARTING,
            Process::STATUS_IN_PROGRESS,
        ));
    }

    /**
     * Log infos about process.
     *
     * @todo Merge with the job processor.
     *
     * @param string $message
     * @param integer $priority
     */
    protected function _log($message, $priority = Zend_Log::DEBUG)
    {
        $priorities = array(
            Zend_Log::EMERG => 'emergency',
            Zend_Log::ALERT => 'alert',
            Zend_Log::CRIT => 'critical',
            Zend_Log::ERR => 'error',
            Zend_Log::WARN => 'warning',
            Zend_Log::NOTICE => 'notice',
            Zend_Log::INFO => 'info',
            Zend_Log::DEBUG => 'debug',
        );
        if (!isset($priorities[$priority])) {
            $priority = Zend_Log::ERR;
        }

        $logs = json_decode(get_option('upgrade_to_omeka_s_process_logs'), true);

        $msg = $message;
        $processor = $this->pluginName;
        $task = '';
        if (strpos($msg, '[') === 0 && $pos = strpos($msg, ']')) {
            $task = substr($msg, 1, $pos - 1);
            $msg = substr($msg, $pos + 1);
        }
        $msg = ltrim($msg, ': ');

        $msg = array(
            'date' => date(DateTime::ISO8601),
            'priority' => $priorities[$priority],
            'processor' => $processor,
            'task' => $task,
            'message' => $msg,
        );
        $logs[] = $msg;
        set_option('upgrade_to_omeka_s_process_logs', json_encode($logs));

        $message = ltrim($message, ': ');
        if (strpos($message, '[') !== 0) {
            $message = ': ' . $message;
        }

        $message = '[UpgradeToOmekaS][' . $this->pluginName . ']' . $message;
        _log($message, $priority);
    }

    /**
     * Transform the given string into a valid URL slug
     *
     * @see SiteSlugTrait::slugify()
     *
     * @param string $input
     * @return string
     */
    protected function _slugify($input)
    {
        $slug = mb_strtolower($input, 'UTF-8');
        $slug = preg_replace('/[^a-z0-9-]+/u', '-', $slug);
        $slug = preg_replace('/-{2,}/', '-', $slug);
        $slug = preg_replace('/-*$/', '', $slug);
        return $slug;
    }
}
