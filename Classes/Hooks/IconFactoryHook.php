<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Hooks;

use TYPO3\CMS\Core\Imaging\IconFactory;
use Zeroseven\Countries\Service\IconService;

class IconFactoryHook implements HookInterface
{
    public function postOverlayPriorityLookup(string $table, array $row, array $status, string $iconName = null): ?string
    {
        if (empty($iconName) && $flagIdentifier = IconService::getRecordFlagIdentifier($table, (int)$row['uid'], $row)) {
            return $flagIdentifier;
        }

        return $iconName;
    }

    public static function register(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][IconFactory::class]['overrideIconOverlay'][] = self::class;
    }
}
