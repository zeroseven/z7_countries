<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Zeroseven\Countries\Exception\BackendException as Exception;

class RegistrationService
{
    public static function enableTable(string $table, string $position = null, string $typeList = null): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['enableColumns'][$table] = [$position, $typeList];
    }

    public static function getTables(): array
    {
        return $GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['enableColumns'] ?? [];
    }

    /** @throws Exception */
    public static function extendInlineChildOverrides(string $foreign_table, string $table, string $field, string $typeList = null): void
    {
        if (!TCAService::hasCountryConfiguration($foreign_table)) {
            throw new Exception('The table "' . $foreign_table . '" has no country configuration.', 1625165947);
        }

        if (
            ($config = $GLOBALS['TCA'][$table]['columns'][$field]['config'] ?? null)
            && isset($config['type'], $config['foreign_table'], $config['overrideChildTca'])
            && $config['type'] === 'inline'
            && $config['foreign_table'] === $foreign_table
        ) {
            foreach (($typeList && $typeList !== '*' ? GeneralUtility::trimExplode(',', $typeList) : array_keys($config['overrideChildTca']['types'])) as $type) {
                if (isset($config['overrideChildTca']['types'][(string)$type])) {
                    $GLOBALS['TCA'][$table]['columns'][$field]['config']['overrideChildTca']['types'][(string)$type]['showitem'] = trim($GLOBALS['TCA'][$table]['columns'][$field]['config']['overrideChildTca']['types'][(string)$type]['showitem'], ',') . ',' . TCAService::getPalette();
                }
            }
        }
    }
}
