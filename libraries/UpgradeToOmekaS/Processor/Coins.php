<?php

/**
 * Upgrade Coins to Omeka S.
 *
 * @package UpgradeToOmekaS
 */
class UpgradeToOmekaS_Processor_Coins extends UpgradeToOmekaS_Processor_Abstract
{

    public $pluginName = 'Coins';
    public $minVersion = '';
    public $maxVersion = '';

    public $module = array(
        'name' => 'Coins',
        'version' => null,
        'url' => 'https://github.com/biblibre/omeka-s-module-Coins/archive/master.zip',
        'size' => null,
        'sha1' => null,
        'type' => 'port',
    );

    public $processMethods = array(
        '_installModule',
    );
}
