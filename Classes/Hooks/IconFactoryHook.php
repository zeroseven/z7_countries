<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Hooks;

use Zeroseven\Countries\Service\IconService;

class IconFactoryHook
{
    public function postOverlayPriorityLookup(string $table, array $row, array $status, string $iconName = null): ?string
    {
        if (empty($iconName)) {

            // If country is not enabled
            if ($table === 'tx_z7countries_country' && empty($row['enabled'])) {
                return 'overlay-locked';
            }

            // Check country configuration of record
            if ($flagIdentifier = IconService::getRecordFlagIdentifier($table, (int)($row['uid'] ?? 0), $row)) {
                return $flagIdentifier;
            }
        }

        return $iconName;
    }
}
