<?php

defined('TYPO3_MODE') || die('🐰');

$GLOBALS['TYPO3_CONF_VARS']['DB']['additionalQueryRestrictions'][\Zeroseven\Countries\Database\QueryRestriction\CountryQueryRestriction::class] = ['disabled' => false];
$GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['disallowedTables'] = ['backend_layout', 'be_dashboards', 'be_groups', 'be_users', 'fe_groups', 'fe_users', 'index_config', 'sys_category', 'sys_collection', 'sys_file', 'sys_filemounts', 'sys_file_collection', 'sys_file_metadata', 'sys_file_reference', 'sys_file_storage', 'sys_language', 'sys_log', 'sys_news', 'sys_note', 'sys_redirect', 'sys_template'];
$GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['cache'] = [];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Site\Entity\Site::class] = [
    'className' => \Zeroseven\Countries\Xclass\Site::class
];
