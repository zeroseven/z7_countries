<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Events;

use TYPO3\CMS\Backend\Controller\EditDocumentController;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Zeroseven\Countries\Service\CountryService;

class AfterFormEnginePageInitializedEvent
{
    protected function getDataCountryRestrictions(EditDocumentController $editDocumentController): array
    {
        $data = $editDocumentController->data;

        if (empty($data)) {
            $data = GeneralUtility::_GP('edit');
        }

        if (($table = (string)array_key_first($data)) && $uid = (int)array_key_first($data[$table] ?? [])) {

            $countries = CountryService::getCountriesByRecord($table, $uid, (array)($data[$table][$uid] ?? []));

            debug($countries, $table . $uid);
        }

        return $data;
    }

    public function checkCountryAndLanguageSettings($x): void
    {
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            '',
            'Test',
            FlashMessage::INFO,
            true
        );

        $this->getDataCountryRestrictions($x->getController());

        $defaultFlashMessageQueue = GeneralUtility::makeInstance(FlashMessageService::class)->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($flashMessage);
    }
}
