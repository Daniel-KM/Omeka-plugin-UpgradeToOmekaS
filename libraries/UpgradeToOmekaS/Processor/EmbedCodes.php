<?php

/**
 * Upgrade Embed Codes to Omeka S.
 *
 * @package UpgradeToOmekaS
 */
class UpgradeToOmekaS_Processor_EmbedCodes extends UpgradeToOmekaS_Processor_Abstract
{

    public $pluginName = 'EmbedCodes';
    public $minVersion = '1.0';
    public $maxVersion = '1.0';

    public $module = array(
        'name' => 'Sharing',
        'version' => '1.0.0',
        'url' => 'https://github.com/omeka-s-modules/Sharing/releases/download/v%s/Sharing-%s.zip',
        'size' => 21384,
        'sha1' => 'b6d4966d3d664448a5cea148641026f4ea81450b',
        'type' => 'equivalent',
        'partial' => true,
        'note' => 'No embedded statistics.',
        'install' => array(
            'settings' => array(
                'sharingServices' => array(
                    'fb', 'twitter', 'tumblr', 'pinterest', 'email', 'embed',
                ),
            ),
        ),
    );

    public $processMethods = array(
        '_installModule',
    );

    protected function _upgradeSettings()
    {
        $target = $this->getTarget();
        // Get current data.
        $sharingMethods = $target->selectSiteSetting('sharing_methods');
        if (is_null($sharingMethods)) {
            $sharingMethods = array(
                'embed',
            );
        }
        // There are some values already, so update them.
        else {
            $sharingMethods[] = 'embed';
            $sharingMethods = array_unique($sharingMethods);
        }
        $target->saveSiteSetting('sharing_methods', $sharingMethods);

        // Set a second option.
        $target->saveSiteSetting('sharing_placement', 'view.show.before');
    }
}
