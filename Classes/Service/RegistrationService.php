<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Service;

class RegistrationService
{
    public static function enableTable(string $table): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['enableColumns'][] = $table;
    }

    public static function getTables(): array
    {
        return $GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['enableColumns'] ?? [];
    }

    public static function enableInlineRecord(string $table, string $inlineRecordTable, string $inlineRecordField)
    {
        $GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['enableInlineRecords'][] = [$table, $inlineRecordTable, $inlineRecordField];
    }

    public static function getInlineRecords(): array
    {
        return $GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['enableInlineRecords'] ?? [];
    }
}
