<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Utility;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class TCAUtility
{
    protected const FIELDNAME_MODE = 'tx_z7countries_mode';

    protected const FIELDNAME_LIST = 'tx_z7countries_list';

    protected const PALETTE_NAME = 'tx_z7countries';

    public static function add(string $table, string $position = null, string $typeList = null): void
    {
        if (isset($GLOBALS['TCA'][$table]) && !isset($GLOBALS['TCA'][$table]['columns'][self::FIELDNAME_MODE], $GLOBALS['TCA'][$table]['columns'][self::FIELDNAME_LIST])) {

            // Add fields to "colums"
            ExtensionManagementUtility::addTCAcolumns($table, [
                self::FIELDNAME_MODE => [
                    'label' => 'LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:*.' . self::FIELDNAME_MODE,
                    'exclude' => true,
                    'l10n_mode' => 'exclude',
                    'onChange' => 'reload',
                    'config' => [
                        'type' => 'select',
                        'renderType' => 'selectSingle',
                        'items' => [
                            ['LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:*.' . self::FIELDNAME_MODE . '.0', '0'],
                            ['LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:*.' . self::FIELDNAME_MODE . '.select', '--div--'],
                            ['LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:*.' . self::FIELDNAME_MODE . '.1', '1'],
                            ['LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:*.' . self::FIELDNAME_MODE . '.2', '2']
                        ],
                        'default' => '0'
                    ]
                ],
                self::FIELDNAME_LIST => [
                    'label' => 'LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:*.' . self::FIELDNAME_LIST,
                    'exclude' => true,
                    'l10n_mode' => 'exclude',
                    'displayCond' => 'FIELD:' . self::FIELDNAME_MODE . ':REQ:true',
                    'config' => [
                        'type' => 'select',
                        'renderType' => 'selectCheckBox',
                        'foreign_table' => 'tx_z7countries_country',
                        'MM' => 'tx_z7countries_country_mm'
                    ]
                ]
            ]);
        }

        // Create palett
        ExtensionManagementUtility::addFieldsToPalette($table, self::PALETTE_NAME, self::FIELDNAME_MODE . ',--linebreak--,' . self::FIELDNAME_LIST);

        // Add new fields to the table
        if (empty($position) && $deletedField = $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled'] ?? null) {
            $position = 'after:' . $deletedField;
        }

        ExtensionManagementUtility::addToAllTCAtypes($table, '--palette--;LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:*.palette.' . self::PALETTE_NAME . ';' . self::PALETTE_NAME, (string)$typeList, (string)$position);
    }
}
