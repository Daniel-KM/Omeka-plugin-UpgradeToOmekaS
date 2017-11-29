<?php

/**
 * Upgrade OpenSeadragon to Omeka S.
 *
 * @package UpgradeToOmekaS
 */
class UpgradeToOmekaS_Processor_OpenSeadragon extends UpgradeToOmekaS_Processor_Abstract
{

    public $pluginName = 'OpenSeadragon';
    public $minVersion = '0.1';
    public $maxVersion = '';

    public $module = array(
        'name' => 'IiifServer',
        'version' => '3.5.9',
        'url' => 'https://github.com/Daniel-KM/Omeka-S-module-IiifServer/releases/download/%s/IiifServer.zip',
        'size' => 832980,
        'sha1' => '358ed63e24fed0d10c7d78b5d48abfef355efc67',
        'type' => 'integrated',
        'note' => 'OpenSeadragon is integrated in Omeka S and the module IIIF Server may create tiles automatically.',
        'install' => array(
            'settings' => array(
                'iiifserver_manifest_description_property' => 'dcterms:bibliographicCitation',
                'iiifserver_manifest_attribution_property' => '',
                'iiifserver_manifest_attribution_default' => 'Provided by Example Organization', // @translate
                'iiifserver_manifest_license_property' => 'dcterms:license',
                'iiifserver_manifest_license_default' => 'http://www.example.org/license.html',
                'iiifserver_manifest_logo_default' => '',
                'iiifserver_manifest_force_url_from' => '',
                'iiifserver_manifest_force_url_to' => '',
                'iiifserver_image_creator' => 'Auto',
                'iiifserver_image_max_size' => 10000000,
                'iiifserver_image_tile_dir' => 'tile',
                'iiifserver_image_tile_type' => 'deepzoom',
            ),
        ),
    );

    public $processMethods = array(
        '_installModule',
    );

    protected function _installModule()
    {
        parent::_installModule();

        $this->_log('[' . __FUNCTION__ . ']: ' . __('OpenSeadragon is integrated in Omeka S and the module IIIF Server may create tiles automatically.'),
            Zend_Log::INFO);
    }
}
