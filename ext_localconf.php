<?php

defined('TYPO3_MODE') || die('🐰');

// Database manipulation
$GLOBALS['TYPO3_CONF_VARS']['DB']['additionalQueryRestrictions'][\Zeroseven\Countries\Database\QueryRestriction\CountryQueryRestriction::class] = ['disabled' => false];

// Add some local extension configuration
$GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['disallowedTables'] = ['backend_layout', 'be_dashboards', 'be_groups', 'be_users', 'fe_groups', 'fe_users', 'index_config', 'sys_category', 'sys_collection', 'sys_file', 'sys_filemounts', 'sys_file_collection', 'sys_file_metadata', 'sys_file_reference', 'sys_file_storage', 'sys_language', 'sys_log', 'sys_news', 'sys_note', 'sys_redirect', 'sys_template', 'tx_z7countries_country'];
$GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['cache'] = [];

// Register hooks
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][TYPO3\CMS\Core\Imaging\IconFactory::class]['overrideIconOverlay'][] = \Zeroseven\Countries\Hooks\IconFactoryHook::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/class.db_list_extra.inc']['actions'][] = \Zeroseven\Countries\Hooks\DatabaseRecordList::class;

// Register xclass objects
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Site\Entity\Site::class] = [
    'className' => \Zeroseven\Countries\Xclass\Site::class
];
