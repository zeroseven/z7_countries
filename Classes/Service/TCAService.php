<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Service;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class TCAService
{
    protected const PALETTE_NAME = 'tx_z7countries';

    protected const FIELD_NAME_MODE = 'tx_z7countries_mode'; // Countries mode, take me home, to the place I belong … 🎶

    protected const FIELD_NAME_LIST = 'tx_z7countries_list';

    public static function isDisallowedTable(string $table): bool
    {
        return in_array($table, $GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['disallowedTables'] ?? [], true);
    }

    public static function addEnableColumns(string $table): void
    {
        if (isset($GLOBALS['TCA'][$table]) && !isset($GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['countries'])) {
            $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['countries'] = [
                'mode' => self::FIELD_NAME_MODE,
                'list' => self::FIELD_NAME_LIST
            ];
        }
    }

    public static function addFields(string $table): void
    {
        if (isset($GLOBALS['TCA'][$table]) && !isset($GLOBALS['TCA'][$table]['columns'][self::FIELD_NAME_MODE], $GLOBALS['TCA'][$table]['columns'][self::FIELD_NAME_LIST])) {
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
                            ['LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:*.' . self::FIELD_NAME_MODE . '.1', '1'],
                            ['LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:*.' . self::FIELD_NAME_MODE . '.2', '2'],
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
                        'foreign_table_where' => 'AND tx_z7countries_country.hidden = 0',
                        'default' => ''
                    ]
                ]
            ]);
        }
    }

    public static function getPalette(): string
    {
        return '--palette--;LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:*.palette.' . self::PALETTE_NAME . ';' . self::PALETTE_NAME;
    }

    public static function addPalette(string $table, string $position = null, string $typeList = null): void
    {
        if (isset($GLOBALS['TCA'][$table]) && !isset($GLOBALS['TCA'][$table]['palettes'][self::PALETTE_NAME])) {
            ExtensionManagementUtility::addFieldsToPalette($table, self::PALETTE_NAME, self::FIELD_NAME_MODE . ',--linebreak--,' . self::FIELD_NAME_LIST);

            // Get position
            if (empty($position) && $deletedField = $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled'] ?? null) {
                $position = 'after:' . $deletedField;
            }

            ExtensionManagementUtility::addToAllTCAtypes($table, self::getPalette(), (string)$typeList, (string)$position);
        }
    }

    public static function getModeColumn(string $table): ?string
    {
        return $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['countries']['mode'] ?? null;
    }

    public static function getListColumn(string $table): ?string
    {
        return $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['countries']['list'] ?? null;
    }

    public static function getEnableColumns(string $table): ?array
    {
        if (($mode = self::getModeColumn($table)) && ($list = self::getListColumn($table))) {
            return ['mode' => $mode, 'list' => $list];
        }

        return null;
    }

    public static function hasCountryConfiguration(string $table): bool
    {
        return self::getModeColumn($table) && self::getListColumn($table);
    }
}
