<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Events;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Controller\Event\AfterFormEnginePageInitializedEvent as Event;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Zeroseven\Countries\Service\CountryService;
use Zeroseven\Countries\Service\TCAService;

class AfterFormEnginePageInitializedEvent
{
    /** @var string */
    protected $table;

    /** @var int */
    protected $uid;

    /** @var array */
    protected $row;

    /** @var int */
    protected $languageUid;

    /** @var int */
    protected $pageUid;

    protected function init(Event $event): void
    {
        $data = $event->getController()->data;

        if (empty($data)) {
            $data = GeneralUtility::_GP('edit');
        }

        $this->table = (string)array_key_first($data);
        $this->uid = (int)array_key_first($data[$this->table] ?? []);
        $this->row = $data[$this->table][$this->uid] ?? null;

        if (!is_array($this->row) || !count($this->row) || !isset($this->row['pid'])) {
            $this->row = (array)BackendUtility::getRecord($this->table, $this->uid);
        }

        $languageField = $GLOBALS['TCA'][$this->table]['ctrl']['languageField'] ?? null;
        $this->languageUid = (int)((is_array($this->row[$languageField]) ? $this->row[$languageField][0] : $this->row[$languageField]) ?? 0);

        $this->pageUid = (int)($this->table === 'pages' ? ($this->languageUid ? $this->row[$this->row['transOrigPointerField']] : $this->row['uid']) : $this->row['pid']);
    }

    protected function getSite(): Site
    {
        return GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($this->pageUid);
    }

    protected function getAvailableCountries(): array
    {
        return CountryService::getCountriesByLanguageUid($this->languageUid, $this->getSite());
    }

    protected function isMatching(): bool
    {
        if (($modeField = TCAService::getModeColumn($this->table)) && (int)($this->row[$modeField] ?? null) === 1) {
            $configuredCountries = CountryService::getCountriesByRecord($this->table, $this->uid, $this->row);
            $availableCountries = $this->getAvailableCountries();
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

    protected function translate(string $key, array $arguments = null): string
    {
        return LocalizationUtility::translate('LLL:EXT:z7_countries/Resources/Private/Language/locallang_be.xlf:' . $key, null, $arguments) ?: $key;
    }

    public function checkCountryAndLanguageSettings(Event $event): void
    {
        $this->init($event);

        if (!$this->isMatching()) {
            $languageTitle = '"' . $this->getSite()->getLanguageById($this->languageUid)->getTitle() . '"';
            $availableCountryNames = implode(', ', array_map(static function ($country) {
                return '"' . $country->getTitle() . '"';
            }, $this->getAvailableCountries()));

            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $this->translate('unavailableLanguage.description', [$languageTitle, $availableCountryNames]),
                $this->translate('unavailableLanguage.title', [$languageTitle]),
                FlashMessage::WARNING,
                true
            );

            $flashMessageQueue = GeneralUtility::makeInstance(FlashMessageService::class)->getMessageQueueByIdentifier();
            $flashMessageQueue->enqueue($flashMessage);
        }
    }
}
