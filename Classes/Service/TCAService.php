<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Service;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class TCAService
{
    protected const PALETTE_NAME = 'tx_z7countries';

    protected const FIELD_NAME_MODE = 'tx_z7countries_mode'; // Countries mode, take me home, to the place I belong … 🎶

    protected const FIELD_NAME_LIST = 'tx_z7countries_list';

    public static function registerPalette(string $table, string $position = null, string $typeList = null): void
    {
        if (isset($GLOBALS['TCA'][$table])) {

            // Extend "enable columns" in tabel ctrl
            $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['countries'] = [
                'mode' => self::FIELD_NAME_MODE,
                'list' => self::FIELD_NAME_LIST
            ];

            // Add fields to table
            if (!isset($GLOBALS['TCA'][$table]['columns'][self::FIELD_NAME_MODE], $GLOBALS['TCA'][$table]['columns'][self::FIELD_NAME_LIST])) {

                ExtensionManagementUtility::addTCAcolumns($table, [
                    self::FIELD_NAME_MODE => [
                        'label' => 'LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:*.' . self::FIELD_NAME_MODE,
                        'exclude' => true,
                        'l10n_mode' => 'exclude',
                        'onChange' => 'reload',
                        'config' => [
                            'type' => 'select',
                            'renderType' => 'selectSingle',
                            'items' => [
                                ['LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:*.' . self::FIELD_NAME_MODE . '.0', '0'],
                                ['LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:*.' . self::FIELD_NAME_MODE . '.select', '--div--'],
                                ['LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:*.' . self::FIELD_NAME_MODE . '.1', '1'],
                                ['LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:*.' . self::FIELD_NAME_MODE . '.2', '2']
                            ],
                            'default' => '0'
                        ]
                    ],
                    self::FIELD_NAME_LIST => [
                        'label' => 'LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:*.' . self::FIELD_NAME_LIST,
                        'exclude' => true,
                        'l10n_mode' => 'exclude',
                        'displayCond' => 'FIELD:' . self::FIELD_NAME_MODE . ':REQ:true',
                        'config' => [
                            'type' => 'select',
                            'renderType' => 'selectCheckBox',
                            'foreign_table' => 'tx_z7countries_country',
                            'default' => ''
                        ]
                    ]
                ]);
            }
        }

        // Create palette and add to types
        if (!isset($GLOBALS['TCA'][$table]['palettes'][self::PALETTE_NAME])) {
            ExtensionManagementUtility::addFieldsToPalette($table, self::PALETTE_NAME, self::FIELD_NAME_MODE . ',--linebreak--,' . self::FIELD_NAME_LIST);

            // Get position
            if (empty($position) && $deletedField = $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled'] ?? null) {
                $position = 'after:' . $deletedField;
            }

            ExtensionManagementUtility::addToAllTCAtypes($table, '--palette--;LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:*.palette.' . self::PALETTE_NAME . ';' . self::PALETTE_NAME, (string)$typeList, (string)$position);
        }
    }
}
