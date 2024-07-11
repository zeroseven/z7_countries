<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Event\Listener;

use TYPO3\CMS\Core\Core\Event\BootCompletedEvent as Event;
use Zeroseven\Countries\Exception\BackendException as Exception;
use Zeroseven\Countries\Service\RegistrationService;
use Zeroseven\Countries\Service\TCAService;

class BootCompletedEvent
{
    /** @throws Exception */
    public function __invoke(Event $event): void
    {
        foreach (RegistrationService::getTables() as $table => [$position, $typeList]) {
            if (TCAService::isDisallowedTable($table)) {
                throw new Exception('The table "' . $table . '" is not supported for country restrictions.', 1625165946);
            }

            TCAService::addEnableColumns($table);
            TCAService::addFields($table);
            TCAService::addPalette($table, $position, $typeList);
        }
    }
}
