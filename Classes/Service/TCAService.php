<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Service;

class TCAService
{
    public const PALETTE_NAME = 'tx_z7countries';
    public const FIELD_KEY_MODE = 'country_mode';
    public const FIELD_NAME_MODE = 'tx_z7countries_mode'; // Countries mode, take me home, to the place I belong … 🎶
    public const FIELD_KEY_LIST = 'country_list';
    public const FIELD_NAME_LIST = 'tx_z7countries_list';

    public static function getModeColumn(string $table): ?string
    {
        return $GLOBALS['TCA'][$table]['ctrl']['enablecolumns'][self::FIELD_KEY_MODE] ?? null;
    }

    public static function getListColumn(string $table): ?string
    {
        return $GLOBALS['TCA'][$table]['ctrl']['enablecolumns'][self::FIELD_KEY_LIST] ?? null;
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
