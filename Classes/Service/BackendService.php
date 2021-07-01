<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Zeroseven\Countries\Exception;

class BackendService
{
    public static function enableConfiguration(string $table, string $position = null, string $typeList = null): void
    {
        if (TCAService::isDisallowedTable($table)) {
            throw new Exception('The table "' . $table . '" is not supported for country restrictions. 🤔', 1621109882);
        }

        TCAService::addEnableColumns($table);
        TCAService::addFields($table);
        TCAService::addPalette($table, $position, $typeList);
    }

    public static function extendInlineChildOverrides(string $foreign_table, string $table, string $field, string $typeList = null): void
    {
        if (!TCAService::hasCountryConfiguration($foreign_table)) {
            throw new Exception('The table "' . $foreign_table . '" has no country configuration. 🤔', 1624626694);
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
