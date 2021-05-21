<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Hooks;

use Zeroseven\Countries\Service\IconService;

class IconFactoryHook
{
    public function postOverlayPriorityLookup(string $table, array $row, array $status, string $iconName = null): ?string
    {
        if (empty($iconName) && $flagIdentifier = IconService::getRecordFlagIdentifier($table, (int)$row['uid'], $row)) {
            return $flagIdentifier;
        }

        return $iconName;
    }
}
