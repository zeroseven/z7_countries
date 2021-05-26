<?php

defined('TYPO3_MODE') || die('✌️');

$GLOBALS['SiteConfiguration']['site_language']['columns']['countries'] = [
    'label' => 'LLL:EXT:z7_countries/Resources/Private/Language/locallang_siteconfiguration.xlf:site_language.country',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectMultipleSideBySide',
        'foreign_table' => 'tx_z7countries_country',
        'min' => 0,
        'size' => 5
    ]
];

foreach ($GLOBALS['SiteConfiguration']['site_language']['types'] ?? [] as $key => $value) {
    $GLOBALS['SiteConfiguration']['site_language']['types'][$key]['showitem'] .= ',countries';
}

