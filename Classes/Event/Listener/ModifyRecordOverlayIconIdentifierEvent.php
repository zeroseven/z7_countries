<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Event\Listener;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Event\ModifyRecordOverlayIconIdentifierEvent as Event;
use Zeroseven\Countries\Service\IconService;

/**
 * Adds country flags to record icons without overriding higher-priority core overlays.
 */
class ModifyRecordOverlayIconIdentifierEvent
{
    /**
     * Applies the country-specific overlay icon identifier.
     */
    public function __invoke(Event $event): void
    {
        if ($event->getOverlayIconIdentifier() !== '') {
            return;
        }

        $table = $event->getTable();
        $row = $event->getRow();

        if ($table === 'tx_z7countries_country') {
            $row['enabled'] ?? ($row = BackendUtility::getRecord($table, (int)($row['uid'] ?? 0)) ?? $row);
            if (empty($row['enabled'])) {
                $event->setOverlayIconIdentifier('overlay-locked');
                return;
            }
        }

        $flagIdentifier = IconService::getRecordFlagIdentifier($table, (int)($row['uid'] ?? 0), $row);
        if ($flagIdentifier !== null) {
            $event->setOverlayIconIdentifier($flagIdentifier);
        }
    }
}
