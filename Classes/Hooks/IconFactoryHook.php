<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Hooks;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use Zeroseven\Countries\Service\TCAService;

class IconFactoryHook
{
    public function postOverlayPriorityLookup(string $table, array $row, array $status, string $iconName = null): ?string
    {
        if (empty($iconName) && $fields = TCAService::getEnableColumn($table)) {
            if (!isset($row[$fields['mode']], $row[$fields['list']]) && $uid = $row['uid'] ?? null) {
                $row = BackendUtility::getRecord($table, $uid);
            }

            if ($row[$fields['mode']]) {
                return 'overlay-country-restriction';
            }
        }

        return $iconName;
    }
}
