<?php

class UpgradeToOmekaS_Form_Main extends Omeka_Form
{
    protected $_unupgradablePlugins = 0;
    protected $_allowThemesOnly = 0;
    protected $_isConfirmation = false;
    protected $_processorCore;

    public function init()
    {
        parent::init();

        $this->setAttrib('id', 'upgrade-to-omeka-s');
        $this->setMethod('post');

        $allowHardLink = $this->_allowHardLink();
        $databasePrefix = get_db()->prefix;
        $sizeDatabase = $this->_getSizeDatabase();

        // TODO Add the confirmation checkboxes only in a second step.
        $this->_isConfirmation = true;

        $validateTrue = array(array(
            'Callback',
            true,
            array(
                'callback' => array('UpgradeToOmekaS_Form_Validator', 'validateTrue'),
            ),
        ));

        $this->addElement('checkbox', 'check_backup_metadata', array(
            'label' => __('Confirm backup of metadata'),
            'description' => __('Check this option to confirm that you just made a backup of your metadata manually.'),
            'required' => true,
            'value' => false,
            'validators' => $validateTrue,
            'errorMessages' => array(__('You should confirm that the database is saved.')),
        ));

        $this->addElement('checkbox', 'check_backup_files', array(
            'label' => __('Confirm backup of files'),
            'description' => __('Check this option to confirm that you just made a backup of your files manually.'),
            'required' => true,
            'value' => false,
            'validators' => $validateTrue,
            'errorMessages' => array(__('You should confirm that the files are saved.')),
        ));

        $this->addElement('checkbox', 'check_backup_check', array(
            'label' => __('Confirm check of backups'),
            'description' => __('Check this option to confirm that you checked your previous backups manually.'),
            'required' => true,
            'value' => false,
            'validators' => $validateTrue,
            'errorMessages' => array(__('You should confirm that the backups are checked.')),
        ));

        $this->addElement('text', 'base_dir', array(
            'label' => __('Base Directory'),
            'description' => __('The abolute real path of the directory on the web server where Omeka Semantic will be installed.')
                . ' ' . __('This directory may exist or not, but it should be writable and empty.')
                . ' ' . __('It can be a subdir of Omeka Classic.'),
            'required' => true,
            'value' => '',
            'filters' => array(
                array('StringTrim', '\s'),
                // Remove the ending trailing directory separator.
                array('PregReplace', array('match' =>'/[\/\\\\\\s]+$/', 'replace' => '')),
                array('Callback', array(
                    'callback' => array('UpgradeToOmekaS_Form_Filter', 'filterRemoveDotSegments'))),
            ),
            'validators' => array(
                array(
                    'Callback',
                    true,
                    array(
                        'callback' => array('UpgradeToOmekaS_Form_Validator', 'validateBaseDir'),
                    ),
                ),
            ),
            'errorMessages' => array(__('The directory should be writable and empty.')),
        ));

        $this->addElement('text', 'installation_title', array(
            'label' => __('Installation Title'),
            'description'   => __('Omeka Semantic can manage multiple sites, so the main install is not a site and requires a title.')
                . ' ' . __('The current single Omeka Classic site will be the first one of Omeka Semantic.'),
            'required' => true,
            'value' => __('Upgrade from Omeka Classic'),
            'filters' => array('StringTrim'),
        ));

        $timeZones = DateTimeZone::listIdentifiers();
        $timeZones = array_combine($timeZones, $timeZones);
        $defaultTimeZone = ini_get('date.timezone') ?: 'UTC';
        $this->addElement('select', 'time_zone', array(
            'label' => __('Time Zone'),
            'description'   => __('Omeka S requires this value.'),
            'multiOptions' => $timeZones,
            'required' => true,
            'value' => $defaultTimeZone,
            'validators' => array(
                array(
                    'Callback',
                    true,
                    array(
                        'callback' => array('UpgradeToOmekaS_Form_Validator', 'validateDateTimeZone'),
                    ),
                ),
            ),
            'errorMessages' => array(__('A time zone is required for Omeka S.')),
        ));

        $this->addElement('radio', 'database_type', array(
            'label' => __('Database'),
            'description'   => __('Define the database Omeka S will be using.'),
            'multiOptions' => array(
                'separate' => __('Use a separate database (recommended)'),
                // 'share' => __('Share the database with a different prefix'),
                'share' => __('Share the database with Omeka Classic'),
            ),
            'required' => true,
            'value' => 'separate',
            'class' => 'offset two columns',
        ));
        $this->addElement('note', 'database_type_note_separate', array(
            'description' => __('When the database is separated, it should be created before process, then the parameters should be set below.')
                // . ' ' . __('"Port" and "prefix" are optional.'),
                . ' ' . __('"Port" is optional.'),
        ));
        $this->addElement('note', 'database_type_note_share', array(
            'description' => __('The tables can coexist in the same database, because Omeka 2 uses a prefix by default and plural names for tables, unlike Omeka S.')
                . ' ' . __('In all cases, a check is done for non standard tables.'),
        ));
        $this->addElement('text', 'database_host', array(
            'label' => __('Host'),
            'filters' => array(array('StringTrim', '/\\\s')),
        ));
        $this->addElement('text', 'database_port', array(
            'label' => __('Port'),
            'filters' => array(array('StringTrim', '/\\\s')),
        ));
        $this->addElement('text', 'database_dbname', array(
            'label' => __('Name'),
            'filters' => array(array('StringTrim', '/\\\s')),
        ));
        $this->addElement('text', 'database_username', array(
            'label' => __('Username'),
            'filters' => array(array('StringTrim', '/\\\s')),
        ));
        $this->addElement('password', 'database_password', array(
            'label' => __('Password'),
            'description' => __('You may have to repeat the password in the second step.'),
            // TODO Clean the form for separate database.
            // 'errorMessages' => array(__('The password is asked twice.')),
        ));
        // Omeka S doesn't allow a table prefix: use doctrine if needed.
        /*
        $this->addElement('text', 'database_prefix', array(
            'label' => __('Table Prefix'),
            'description'   => __('When the database is shared, the prefix of the tables should be different from the existing one ("%s").',
                    $databasePrefix ?: __('none'))
                . ' ' . __('It can be empty for a separate database.'),
            'filters' => array(array('StringTrim', '/\\\s')),
            'validators' => array(
                array(
                    'Callback',
                    true,
                    array(
                        'callback' => array('UpgradeToOmekaS_Form_Validator', 'validatePrefix'),
                    ),
                ),
            ),
            'errorMessages' => array(__('A prefix should have only alphanumeric characters, no space, and end with an underscore "_".')),
        ));
        */
        // An hidden value is set, but it won't be used.
        $this->addElement('hidden', 'database_prefix', array(
            'value' => $databasePrefix == 'omekas_' ? 'omekasemantic_' : 'omekas_',
        ));
        // $this->addElement('note', 'database_prefix_note', array(
        //     'description' => __('Currently, Omeka S doesn’t allow to use a prefix.'),
        // ));

        $multiOptions = array();
        if ($allowHardLink) {
            $multiOptions['copy'] = __('Copy');
            $multiOptions['hard_link'] = __('Hard Link');
            $multiOptions['dummy'] = __('Dummy files');
            $multiOptions['dummy_hard'] = __('Dummy files (hard link)');
            $description = __('Define what to do with files of the archive (original files, thumbnails, etc.).')
                . ' ' . __('To create hard links avoids to waste space and to speed copy on the same disk.')
                . ' ' . __('The dummy files can be used for testing purposes.')
                . ' ' . __('Original files are never modified or deleted.')
                . ' ' . __('It seems the server allows hard links, but a second check will be done to avoid issues with mounted volumes.');
        }
        // No hard link.
        else {
            $multiOptions['copy'] = __('Copy');
            $multiOptions['dummy'] = __('Dummy files');
            $description = __('Define what to do with files of the archive (original files, thumbnails, etc.).')
                . ' ' . __('The dummy files can be used for testing purposes.')
                . ' ' . __('Original files are never modified or deleted.')
                . ' ' . __('The server does not support hard linking.');
        }
        $this->addElement('radio', 'files_type', array(
            'label' => __('Files'),
            'description' => $description,
            'multiOptions' => $multiOptions,
            'required' => true,
            'value' => $allowHardLink ? 'hard_link' : 'copy',
            'class' => 'offset two columns',
        ));

        $usedRoles = $this->_getUsedRoles();
        $roles = $this->_getTargetRoles();
        $roles = label_table_options($roles, '');
        $mapping = $this->_getDefaultMappingRoles();
        $roleNames = array();
        $i = 0;
        foreach ($usedRoles as $usedRole) {
            $roleName = 'mapping_role_' . $usedRole['role'];
            $roleNames[] = $roleName;

            $description = $usedRole['total_users'] == 0
                ?__('No user has this role.')
                : ($usedRole['total_users'] == 1
                    ? __('%d user has this role.', $usedRole['total_users'])
                    :  __('%d users have this role.', $usedRole['total_users']));
            if ($usedRole['role'] === 'guest' && $usedRole['total_users']) {
                $description .= ' ' . __('The module "GuestUser" will be automatically installed.');
                if (!plugin_is_active('GuestUser')) {
                    $description .= ' ' . __('To upgrade params and new user tokens, the plugin "GuestUser" must be active in Omeka Classic.');
                }
            }

            $this->addElement('select', $roleName, array(
                'label' => __($usedRole['role']),
                'multiOptions' => $roles,
                'value' => isset($mapping[$usedRole['role']])
                    ? $mapping[$usedRole['role']]
                    : '',
                'description' => $description,
            ));
        }
        // Add a note for empty used roles. It simplifies the display group.
        if (empty($usedRoles)) {
            $roleName = 'item_types_note';
            $this->addElement('note', $roleName, array(
                'description' => __('This site doesn’t use roles.'),
            ));
            $roleNames[] = $roleName;
        }

        $usedItemTypes = $this->_getUsedItemTypes();
        $classes = $this->_getClassesByVocabulary();
        $mapping = $this->_getDefaultMappingItemTypesToClasses();
        $itemTypeNames = array();
        $i = 0;
        foreach ($usedItemTypes as $usedItemType) {
            $itemTypeName = 'mapping_item_type_' . $usedItemType['item_type_id'];
            $itemTypeNames[] = $itemTypeName;

            if ($usedItemType['total_items'] > 0) {
                $descriptionLink = '<a href="' . url('/items/browse', array(
                    'type' => $usedItemType['item_type_id'],
                )) . '">';
                $description = $usedItemType['total_items'] == 1
                    ? __('%sOne item%s uses this item type.', $descriptionLink, '</a>')
                    : __('%s%d items%s use this item type.', $descriptionLink, $usedItemType['total_items'], '</a>');
            }
            // Not used (so useless here).
            else {
                $description = __('This item type is not used.');
            }

            $this->addElement('select', $itemTypeName, array(
                'label' => __($usedItemType['item_type_name']),
                'multiOptions' => $classes,
                'value' => isset($mapping[$usedItemType['item_type_id']])
                    ? $mapping[$usedItemType['item_type_id']]
                    : '',
                'description' => $description,
            ));
        }
        // Add a note for empty used item types. It simplifies the display group too.
        if (empty($usedItemTypes)) {
            $itemTypeName = 'item_types_note';
            $this->addElement('note', $itemTypeName, array(
                'description' => __('This site doesn’t use item types.'),
            ));
            $itemTypeNames[] = $itemTypeName;
        }

        $usedElements = $this->_getUsedElements();
        $itemTypesByUsedElements = $this->_getItemTypesByUsedElement();
        $properties = $this->_getPropertiesByVocabulary();
        $mapping = $this->_getDefaultMappingElementsToProperties();
        $elementNames = array();
        $isOldOmeka = version_compare(OMEKA_VERSION, '2.5', '<');
        $previousElementSetName = null;
        $i = 0;
        foreach ($usedElements as $usedElement) {
            if ($previousElementSetName != $usedElement['element_set_name']) {
                $elementName = 'element_note_element_set_' . ++$i;
                $elementNames[] = $elementName;
                $this->addElement('note', $elementName, array(
                    'label' => __($usedElement['element_set_name']),
                    'description' => '<em>' . __('Elements below belong to the element set "%s".',
                        __($usedElement['element_set_name'])) . '</em>',
                ));
            }
            $elementName = 'mapping_element_' . $usedElement['element_id'];
            $elementNames[] = $elementName;
            $description = '';
            if ($usedElement['total_items'] > 0) {
                // For "Item Type Metadata", add some infos about types.
                $descriptionItemTypes = '';
                if ($usedElement['element_set_name'] == 'Item Type Metadata'
                        // The element may be used by a collection or a file.
                        && isset($itemTypesByUsedElements[$usedElement['element_id']])
                    ) {
                    $itemTypesForUsedElement = $itemTypesByUsedElements[$usedElement['element_id']];
                    $descriptionItemTypes = array();
                    foreach ($itemTypesForUsedElement as $itemTypeName => $itemTypeForElementValues) {
                        if ($isOldOmeka) {
                            $descriptionItemTypes[] = $itemTypeForElementValues['item_type_name'] . ' (' . $itemTypeForElementValues['total'] . ')';
                        }
                        // Omeka 2.5.
                        else {
                            $descriptionItemTypes[] = '<a href="' . url('/items/browse', array(
                                'advanced[0][element_id]' => $usedElement['element_id'],
                                'advanced[0][type]' => 'is not empty',
                                'type' => $itemTypeForElementValues['item_type_id'],
                            )) . '">' . $itemTypeForElementValues['item_type_name'] . '</a> (' . $itemTypeForElementValues['total'] . ')';
                        }
                    }
                    $descriptionItemTypes = count($descriptionItemTypes) <= 1
                        ? ' ' . __('as item type %s', implode(', ', $descriptionItemTypes))
                        : ' ' . __('as item types %s', implode(', ', $descriptionItemTypes));
                }

                if ($isOldOmeka) {
                    $description = function_exists('plural')
                        ? __(plural('One item uses this element%s.', '%d items use this element%s.', $usedElement['total_items']), $usedElement['total_items'], $descriptionItemTypes)
                        : __('%d items use this element%s.', $usedElement['total_items'], $descriptionItemTypes);
                    }
                // Omeka 2.5.
                else {
                    $linkStart = '<a href="' . url('/items/browse', array(
                                'advanced[0][element_id]' => $usedElement['element_id'],
                                'advanced[0][type]' => 'is not empty',
                            )) .  '"><em>';
                    if ($usedElement['total_items'] <= 1) {
                        $description = __('%s%d item%s uses this element%s.',
                            $linkStart,
                            $usedElement['total_items'],
                            '</em></a>',
                            $descriptionItemTypes);
                    } else {
                        $description = __('%s%d items%s use this element%s.',
                            $linkStart,
                            $usedElement['total_items'],
                            '</em></a>',
                            $descriptionItemTypes);
                    }
                }
            }
            // Element with no item.
            else {
                $description = __('No item uses this element.');
            }
            $description .= ' ';
            if ($usedElement['total_collections'] && $usedElement['total_files']) {
                $description .= __('%d collections and %d files use this element.',
                    $usedElement['total_collections'], $usedElement['total_files']);
            }
            elseif ($usedElement['total_collections']) {
                $description .= $usedElement['total_collections'] <= 1
                    ? __('One collection uses this element.')
                    : __('%d collections use this element.',
                        $usedElement['total_collections']);
            }
            elseif ($usedElement['total_files']) {
                $description .= $usedElement['total_files'] <= 1
                    ? __('One file uses this element.')
                    : __('%d files use this element.',
                        $usedElement['total_files']);
            }
            $this->addElement('select', $elementName, array(
                'label' => __($usedElement['element_name']),
                'multiOptions' => $properties,
                'value' => isset($mapping[$usedElement['element_id']])
                    ? $mapping[$usedElement['element_id']]
                    : '',
                'description' => $description,
            ));
            $previousElementSetName = $usedElement['element_set_name'];
        }
        // Add a note for empty elements. It simplifies the display group too.
        if (empty($usedElements)) {
            $elementName = 'elements_note';
            $this->addElement('note', $elementName, array(
                'description' => __('This site doesn’t use any element.'),
            ));
            $elementNames[] = $elementName;
        }

        $totalTags = total_records('Tag');
        $tagging = plugin_is_active('Tagging');
        $this->addElement('checkbox', 'install_folksonomy', array(
            'label' => __('Tags To Folksonomy'),
            'description' => __('Tags were removed in Omeka S in order to use only the Dublin Core Subject or another standard element.')
                . ' ' . __('A module replaces this core feature: %sFolksonomy%s.',
                    '<a href="https://github.com/Daniel-KM/Omeka-S-module-Folksonomy" target="_blank">', '</a>')
                . ' ' . ($tagging
                    ? __('The plugin "Tagging" is enabled, so the module will be automatically installed.')
                    : __('It can be installed automatically.')
                        . ($totalTags? ' ' . __('Without it, the %d tags will be lost.', $totalTags) : '')
                ),
            'required' => false,
            'value' => (boolean) $totalTags,
            'disabled' => $tagging,
        ));

        $this->addElement('text', 'first_user_password', array(
            'label' => __('First User Password'),
            'description'   => __('The password of users will be lost and they have to request a new one on the login form.')
                . ' ' . __('You may enter the password of the first user here to access directly to admin board of Omeka S.'),
            'required' => false,
            'value' => '',
            'filters' => array('StringTrim'),
        ));

        $this->addElement('checkbox', 'skip_error_metadata', array(
            'label' => __('Skip error in metadata'),
            'description' => __('With an old install or a badly managed server, the database may contain element texts that doesn’t belong to any records anymore.')
                . ' ' . __('These metadata cannot be upgraded, so the process will stop, unless you check this box.')
                . ' ' . __('This option may be used in plugins too.')
                . ' ' . __('In all cases, the ids of the bad data are logged.'),
            'required' => false,
            'value' => false,
        ));

        $this->addElement('checkbox', 'skip_hash_files', array(
            'label' => __('Skip Hash Files'),
            'description' => __('To check integrity of files, the hashing algorithm is md5 in Omeka Classic and sha256 in Omeka S.')
                . ' ' . __('This process is the longer upgrade step.')
                . ' ' . __('If checked, the hashing will be skipped, so you’ll have to hash files directly in Omeka S, if wished.')
                . ' ' . __('The hashing is the last step in the upgrade process, so you will be able to try Omeka S even if the process is not ended.'),
            'required' => false,
            'value' => false,
        ));

        if (empty($this->_unupgradablePlugins)) {
            $this->addElement('hidden', 'plugins_confirm_unupgradable', array(
                'value' => true,
            ));
        }
        // Some plugins are not upgradable.
        else {
            $this->addElement('checkbox', 'plugins_confirm_unupgradable', array(
                'label' => __('Skip unupgradable plugins'),
                'description' => __('Check this option to process the upgrade without unupgradable plugins.'),
                'required' => true,
                'value' => false,
                'validators' => $validateTrue,
                'errorMessages' => array(__('You should confirm that you want to upgrade Omeka even with unupgradable plugins.')),
            ));
        }

        if ($this->_isConfirmation) {
            $this->addElement('checkbox', 'check_confirm_backup', array(
                'label' => __('Check of database size'),
                'description' => __('I confirm that the file system where the database is can manage %dMB of new data (two times the Omeka Classic one).', ceil($sizeDatabase * 2 / 1024 / 1024)),
                // 'required' => true,
                'value' => false,
                // 'validators' => $validateTrue,
                'errorMessages' => array(__('This check is required to confirm that you understand that some checks cannot be done automatically with some configurations.')),
            ));

            $this->addElement('checkbox', 'check_confirm_license', array(
                'label' => __('Confirm'),
                'description' => __('I read the license (see the readme), I agree to it, and, like for any proprietary software, I confirm that I am solely and entirely responsible of what I do.'),
                // 'required' => true,
                'value' => false,
                // 'validators' => $validateTrue,
                'errorMessages' => array(__('This checkbox must be checked if you understand what you do.')),
            ));
        }

        $this->addDisplayGroup(
            array(
                'check_backup_metadata',
                'check_backup_files',
                'check_backup_check',
            ),
            'check_backup',
            array(
                'legend' => __('Backup of Metadata and Files'),
                'description' => __('The only possible issues for Omeka Classic are related to the lack of disk space for the file system, the temp directory or the database directory.')
                    . ' ' . __('An automatic check will be done before the confirmation, except for the file system where the database and the logs are.'),
        ));

        $this->addDisplayGroup(
            array(
                'base_dir',
                'installation_title',
                'time_zone',
            ),
            'general',
            array(
                'legend' => __('General Settings of Omeka Semantic'),
        ));

        $this->addDisplayGroup(
            array(
                'database_type',
                'database_type_note_separate',
                'database_type_note_share',
                'database_prefix_note',
                'database_host',
                'database_port',
                'database_dbname',
                'database_username',
                'database_password',
                'database_prefix',
            ),
            'database',
            array(
                'legend' => __('Database for Omeka Semantic'),
        ));

        $this->addDisplayGroup(
            array(
                'files_type',
            ),
            'files',
            array(
                'legend' => __('Files for Omeka Semantic'),
        ));

        $description = '';
        if (count($usedRoles)) {
            $description = __('By default, Omeka S uses six roles from researcher (the lowest person) to the global administrator.')
                . ' ' . __('The current user will be the global admin.')
                . ' ' . __('The users with a role that is not mapped won’t be upgraded.')
                . ' ' . __('The role "anonymous" is not used in Omeka S Core, but may be used to keep data temporarily.')
                . '<br /><br /><button id="display-mapped-roles" class="green button" name="display-mapped-roles" type="button" value="show">' . __('Hide/show mapped roles') . '</button>';
        }
        $this->addDisplayGroup(
            $roleNames,
            'roles',
            array(
                'legend' => __('Mapping of roles'),
                'description' => $description,
        ));

        $description = '';
        if (count($usedItemTypes)) {
            $description = __('By default, Omeka S uses four vocabularies to define classes: "Dublin Core", "Dublin Core Type", "Bibliographic Ontology" and  "Friend of a Friend".')
                . ' ' . __('So, %sused%s item types of Omeka C should be mapped to the %d classes of these vocabularies.',
                    '<em>', '</em>', 105)
                . ' ' . __('The preset is the one used by the module Omeka2Importer.')
                . ' ' . __('The items with an item type that is not mapped won’t have a class.')
                . '<br />' . __('Currently, no new class can be added during the upgrade process.')
                . '<br /><br /><button id="display-mapped-item-types" class="green button" name="display-mapped-item-types" type="button" value="show">' . __('Hide/show mapped item types') . '</button>';
        }
        $this->addDisplayGroup(
            $itemTypeNames,
            'item_types',
            array(
                'legend' => __('Mapping of item types to classes'),
                'description' => $description,
        ));

        $description = '';
        if (count($usedElements)) {
            $description = __('By default, Omeka S allows only these semantic vocabularies: "Dublin Core", "Bibliographic Ontology" and  "Friend of a Friend".')
                . ' ' . __('So, %sused%s elements of Omeka C should be mapped to the %d properties of these vocabularies.',
                    '<em>', '</em>', 184)
                . ' ' . __('The preset is the one used by the module Omeka2Importer.')
                . ' ' . __('Elements that are not mapped won’t be upgraded.')
                . '<br />' . __('Currently, no new vocabulary can be added during the upgrade process.')
                . '<br /><br /><button id="display-mapped-elements" class="green button" name="display-mapped-elements" type="button" value="show">' . __('Hide/show mapped elements'). '</button>';
        }
        $this->addDisplayGroup(
            $elementNames,
            'elements',
            array(
                'legend' => __('Mapping of elements to properties'),
                'description' => $description,
        ));

        $this->addDisplayGroup(
            array(
                'plugins_confirm_unupgradable',
            ),
            'modules',
            array(
                'legend' => __('Modules of Omeka Semantic'),
                'description' => $this->_unupgradablePlugins
                    ? __('Some plugins (%d) are not upgradable.', $this->_unupgradablePlugins)
                    : __('All plugins are upgradable.'),
        ));

        $this->addDisplayGroup(
            array(
                'install_folksonomy',
                'first_user_password',
                'skip_error_metadata',
                'skip_hash_files',
            ),
            'various',
            array(
                'legend' => __('Various'),
        ));

        if ($this->_isConfirmation) {
            $this->addDisplayGroup(
                array(
                    'check_confirm_backup',
                    'check_confirm_license',
                ),
                'confirm',
                array(
                    'legend' => __('Confirmation'),
            ));
        }

        $this->applyOmekaStyles();
        $this->setAutoApplyOmekaStyles(false);

        $this->addElement('sessionCsrfToken', 'csrf_token');

        if ($this->_isConfirmation) {
            $this->addElement('submit', 'submit', array(
                'label' => __('Submit'),
                'class' => 'submit submit-big red',
                'decorators' => (array(
                    'ViewHelper',
                    array('HtmlTag', array('tag' => 'div', 'class' => 'field')))),
            ));
            if ($this->_allowThemesOnly) {
                $this->addElement('submit', 'submit_themes', array(
                    'label' => __('Themes only'),
                    'class' => 'submit submit-big blue',
                    'decorators' => (array(
                        'ViewHelper',
                        array('HtmlTag', array('tag' => 'div', 'class' => 'field')))),
                ));
            }
        }
        // Simple check.
        else {
            $this->addElement('submit', 'check_params', array(
                'label' => __('Check Parameters'),
                'class' => 'submit submit-big',
                'decorators' => (array(
                    'ViewHelper',
                    array('HtmlTag', array('tag' => 'div', 'class' => 'field')))),
            ));
        }
    }

    /**
     * Set the param "unupgradablePlugins".
     *
     * @param boolean $value
     */
    public function setUnupgradablePlugins($value)
    {
        $this->_unupgradablePlugins = $value;
    }

    /**
     * Set the param "allowThemesOnly".
     *
     * @param boolean $value
     */
    public function setAllowThemesOnly($value)
    {
        $this->_allowThemesOnly = $value;
    }

    /**
     * Set if the form is a confirmation one.
     */
    public function setIsConfirmation($value)
    {
        $this->_isConfirmation = (boolean) $value;
    }

    /**
     * Validate the form
     *
     * @todo Move checks from the Core.
     *
     * @param  array $data
     * @throws Zend_Form_Exception
     * @return bool
     */
    public function isValid($data)
    {
        $valid = parent::isValid($data);

        $databaseType = $this->getElement('database_type');
        switch ($databaseType->getValue()) {
            case 'separate':
                foreach (array(
                        'database_host' => __('host'),
                        'database_username' => __('user name'),
                        'database_dbname' => __('name'),
                    ) as $name => $text) {
                    $element = $this->getElement($name);
                    $value = $element->getValue();
                    if (empty($value)) {
                        $message = __('The database parameter "%s" should be filled when the database is separate.', $text);
                        $element->addError($message);
                        $valid = false;
                    }
                }
                break;

            case 'share':
                $databasePrefix = $this->getElement('database_prefix');
                if ($databasePrefix->getValue() == get_db()->prefix) {
                    $message = __('In a shared database, the prefix cannot be the same for Omeka Classic and Omeka Semantic.');
                    $databasePrefix->addError($message);
                    $valid = false;
                }
                break;

            default:
                $message = __('Value %s is not allowed as database type.');
                $databaseType->addError($message);
                $valid = false;
                break;
        }

        return $valid;
    }

    /**
     * Helper to get the a value from db.ini.
     *
     * @return string
     */
    protected function _getDatabaseValue($name)
    {
        $db = get_db();
        $config = $db->getAdapter()->getConfig();
        return isset($config[$name]) ? $config[$name] : '';
    }

    /**
     * Check if a hard link can be created.
     *
     * @return string
     */
    protected function _allowHardLink()
    {
        // A test is done inside the thumbnails folder, always writable.
        $base = FILES_DIR . DIRECTORY_SEPARATOR . 'thumbnails';
        $target = $base . DIRECTORY_SEPARATOR . 'index.html';
        $link = $base . DIRECTORY_SEPARATOR . md5(rtrim(strtok(substr(microtime(), 2), ' '), '0'));
        $result = @link($target, $link);
        if ($result) {
            unlink($link);
        }
        return $result;
    }

    /**
     * Get the current size of the Omeka Classic database.
     *
     * @return integer
     */
    protected function _getSizeDatabase()
    {
        try {
            $db = get_db();
            $config = $db->getAdapter()->getConfig();
            $dbName = $config['dbname'];
            if (empty($dbName)) {
                return 0;
            }

            $sql = 'SELECT SUM(data_length + index_length + data_free) AS "Size"
            FROM information_schema.TABLES
            WHERE table_schema = "' . $dbName . '";';
            return $db->fetchOne($sql);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get an array containing all used roles with total.
     *
     * @return array.
     */
    protected function _getUsedRoles()
    {
        $processor = new UpgradeToOmekaS_Processor_CoreSite();
        $result = $processor->getUsedRoles();
        return $result;
    }

    /**
     * Get the list of roles of Omeka S.
     *
     * @internal To use Omeka S is not possible, since it's not installed yet.
     *
     * @return array Array of values suitable for a dropdown menu.
     */
    protected function _getTargetRoles()
    {
        $dataDir = dirname(dirname(dirname(dirname(__FILE__))))
            . DIRECTORY_SEPARATOR . 'libraries'
            . DIRECTORY_SEPARATOR . 'data';

        $script = $dataDir
            . DIRECTORY_SEPARATOR . 'roles.php';
        $roles = require $script;
        return $roles;
    }

    /**
     * Get the mapping of roles from Omeka C to Omeka S.
     *
     * @return array
     */
    protected function _getDefaultMappingRoles()
    {
        $processor = new UpgradeToOmekaS_Processor_CoreSite();
        $result = $processor->getDefaultMappingRoles();
        return $result;
    }

    /**
     * Get the processor for core Elements.
     *
     * @return UpgradeToOmekaS_Processor_CoreElements
     */
    protected function _getProcessorCoreElements()
    {
        if (empty($this->_processorCore)) {
            $this->_processorCore = new UpgradeToOmekaS_Processor_CoreElements();
        }
        return $this->_processorCore;
    }

    /**
     * Get an array containing all used item types with total.
     *
     * @return array.
     */
    protected function _getUsedItemTypes()
    {
        $processor = $this->_getProcessorCoreElements();
        $result = $processor->getUsedItemTypes();
        return $result;
    }

    /**
     * Get the list of classes of all vocabularies of Omeka S.
     *
     * @internal To use Omeka S is not possible, since it's not installed yet.
     *
     * @return array Array of values suitable for a dropdown menu.
     */
    protected function _getClassesByVocabulary()
    {
        $processor = $this->_getProcessorCoreElements();
        $classes = $processor->getClasses();
        $result = $this->_getSelectOptionsForVocabularies($classes);
        return $result;
    }

    /**
     * Get the mapping from Omeka C element ids to Omeka S property ids.
     *
     * @return array
     */
    protected function _getDefaultMappingItemTypesToClasses()
    {
        $processor = $this->_getProcessorCoreElements();
        $result = $processor->getDefaultMappingItemTypesToClasses('id', 'prefix:name');
        return $result;
    }

    /**
     * Get an array containing all used elements with total by record type.
     *
     * @return array.
     */
    protected function _getUsedElements()
    {
        $processor = $this->_getProcessorCoreElements();
        $result = $processor->getUsedElements();
        return $result;
    }

    /**
     * Get an array containing all item types by used element.
     *
     * @return array.
     */
    protected function _getItemTypesByUsedElement()
    {
        $processor = $this->_getProcessorCoreElements();
        $result = $processor->getItemTypesByUsedElement();
        return $result;
    }

    /**
     * Get the list of properties of all vocabularies of Omeka S.
     *
     * @internal To use Omeka S is not possible, since it's not installed yet.
     *
     * @return array Array of values suitable for a dropdown menu.
     */
    protected function _getPropertiesByVocabulary()
    {
        $processor = $this->_getProcessorCoreElements();
        $properties = $processor->getProperties();
        $result = $this->_getSelectOptionsForVocabularies($properties);
        return $result;
    }

    /**
     * Get the mapping from Omeka C element ids to Omeka S property ids.
     *
     * @return array
     */
    protected function _getDefaultMappingElementsToProperties()
    {
        $processor = $this->_getProcessorCoreElements();
        $result = $processor->getDefaultMappingElementsToProperties('id', 'prefix:name', false);
        return $result;
    }

    /**
     * Helper to get a list of values suitable for a two levels dropdown menu.
     *
     * @param array $source
     * @return array
     */
    protected function _getSelectOptionsForVocabularies($source)
    {
        $result = array();
        foreach ($source as $vocabularyLabel => $vocabulary) {
            // Use only the prefix of the vocabulary.
            $prefix = trim(substr($vocabularyLabel, strpos($vocabularyLabel, '[')), '[] ');
            // Preformat the source.
            foreach ($vocabulary as $id => $value) {
                $label = reset($value);
                $name = key($value);
                $result[$vocabularyLabel][$prefix . ':' . $name] = $label;
            }
            asort($result[$vocabularyLabel], SORT_NATURAL | SORT_FLAG_CASE);
        }
        $result = label_table_options($result, '');
        return $result;
    }
}
