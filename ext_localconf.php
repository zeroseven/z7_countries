<?php

defined('TYPO3_MODE') || die('🐰');

call_user_func(static function () {

    // Add typoscript constants
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup('config.linkVars := addToList(' . \Zeroseven\Countries\Service\ParameterService::COUNTRY_PARAMETER . '(0-9999))');
});

$GLOBALS['TYPO3_CONF_VARS']['DB']['additionalQueryRestrictions'][\Zeroseven\Countries\Database\QueryRestriction\CountryQueryRestriction::class] = ['disabled' => false];
$GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['disallowedTables'] = ['backend_layout', 'be_dashboards', 'be_groups', 'be_users', 'fe_groups', 'fe_users', 'index_config', 'sys_category', 'sys_collection', 'sys_file', 'sys_filemounts', 'sys_file_collection', 'sys_file_metadata', 'sys_file_reference', 'sys_file_storage', 'sys_language', 'sys_log', 'sys_news', 'sys_note', 'sys_redirect', 'sys_template'];
