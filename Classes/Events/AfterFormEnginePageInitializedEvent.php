<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Events;

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AfterFormEnginePageInitializedEvent
{
    public function checkCountryAndLanguageSettings($x): void
    {
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            '',
            'Test',
            FlashMessage::INFO,
            true
        );

        debug($x);

        $defaultFlashMessageQueue = GeneralUtility::makeInstance(FlashMessageService::class)->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($flashMessage);
    }
}
