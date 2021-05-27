<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Events;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Controller\Event\AfterFormEnginePageInitializedEvent as Event;
use Zeroseven\Countries\Service\CountryService;
use Zeroseven\Countries\Service\TCAService;

class AfterFormEnginePageInitializedEvent
{
    protected function getAvailableCountries(string $table, int $uid, array $row = null): ?array
    {
        $languageField = $GLOBALS['TCA'][$table]['ctrl']['languageField'] ?? null;
        $languageId = (int)((is_array($row[$languageField]) ? $row[$languageField][0] : $row[$languageField]) ?? 0);
        $pageId = (int)($table === 'pages' ? ($languageId ? $row[$row['transOrigPointerField']] : $row['uid']) : $row['pid']);
        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($pageId);

        return CountryService::getCountriesByLanguageUid($languageId, $site);
    }

    protected function isMatching(string $table, int $uid, array $row = null): bool
    {
        if (
            ($modeField = TCAService::getModeColumn($table))
            && (int)($row[$modeField] ?? null) === 1
            && $configuredCountries = CountryService::getCountriesByRecord($table, $uid, $row)
        ) {
            $availableCountries = $this->getAvailableCountries($table, $uid, $row);
            $availableCountryUids = array_map(static function ($country) {
                return $country->getUid();
            }, $availableCountries);

            foreach ($configuredCountries as $country) {
                if (in_array($country->getUid(), $availableCountryUids, true)) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    public function checkCountryAndLanguageSettings(Event $configuration): void
    {
        $data = $configuration->getController()->data;

        if (empty($data)) {
            $data = GeneralUtility::_GP('edit');
        }

        $table = (string)array_key_first($data);
        $uid = (int)array_key_first($data[$table] ?? []);
        $row = $data[$table][$uid] ?? null;

        if (!is_array($row) || !count($row) || !isset($row['pid'])) {
            $row = (array)BackendUtility::getRecord($table, $uid);
        }

        if (!$this->isMatching($table, $uid, $row)) {
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                '',
                ':(',
                FlashMessage::INFO,
                true
            );

            $defaultFlashMessageQueue = GeneralUtility::makeInstance(FlashMessageService::class)->getMessageQueueByIdentifier();
            $defaultFlashMessageQueue->enqueue($flashMessage);
        }
    }
}
