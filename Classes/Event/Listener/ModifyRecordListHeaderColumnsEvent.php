<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Event\Listener;

use TYPO3\CMS\Backend\RecordList\Event\ModifyRecordListHeaderColumnsEvent as Event;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Zeroseven\Countries\Service\CountryService;
use Zeroseven\Countries\Service\IconService;
use Zeroseven\Countries\Service\TCAService;

class ModifyRecordListHeaderColumnsEvent
{
    protected function getCountryParameter(): int
    {
        return (int)($_GET[ModifyDatabaseQueryForRecordListingEvent::PARAMETER] ?? 0);
    }

    protected function translate(string $key): string
    {
        if (isset($GLOBALS['LANG']) && $GLOBALS['LANG'] instanceof LanguageService) {
            return htmlspecialchars($GLOBALS['LANG']->sL($key));
        }

        return '';
    }

    public function __invoke(Event $event): void
    {
        if (TCAService::hasCountryConfiguration($event->getTable())) {
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            $buttonBar = GeneralUtility::makeInstance(ButtonBar::class);
            $columns = $event->getColumns();

            // Collect buttons
            foreach (CountryService::getAllCountries() ?: [] as $country) {
                $active = $country->getUid() === $this->getCountryParameter();
                $url = $uriBuilder->buildUriFromRoute('web_list', [
                    'table' => $event->getTable(),
                    'id' => $event->getRecordList()->id,
                    ModifyDatabaseQueryForRecordListingEvent::PARAMETER => $active ? 0 : $country->getUid()
                ]);

                $buttonBar->addButton($buttonBar->makeLinkButton()
                    ->setHref($url)
                    ->setTitle($country->getTitle())
                    ->setShowLabelText($active)
                    ->setIcon(IconService::getCountryIcon($country, null, $active ? 'overlay-readonly' : '')), null, $active ? 1 : 2);
            }

            // Render button bar
            if ($buttons = $buttonBar->getButtons()) {
                $columns['_CONTROL_'] .= $this->translate('LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:tx_z7countries_country');

                foreach ($buttons as $groups) {
                    foreach ($groups as $group) {
                        $columns['_CONTROL_'] .= ' ' . implode('', $group);
                    }
                }
            }

            $event->setColumns($columns);
        }
    }
}
