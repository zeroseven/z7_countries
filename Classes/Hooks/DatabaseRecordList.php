<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Hooks;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Recordlist\RecordList\RecordListHookInterface;
use Zeroseven\Countries\Database\QueryRestriction\CountryQueryRestriction;
use Zeroseven\Countries\Service\CountryService;
use Zeroseven\Countries\Service\IconService;
use Zeroseven\Countries\Service\TCAService;

class DatabaseRecordList implements RecordListHookInterface
{
    protected const PARAMETER = 'tx_z7country';

    protected function getCountryParameter(): int
    {
        return (int)(GeneralUtility::_GET(self::PARAMETER) ?: 0);
    }

    protected function translate(string $key): string
    {
        if (isset($GLOBALS['LANG']) && $GLOBALS['LANG'] instanceof LanguageService) {
            return htmlspecialchars($GLOBALS['LANG']->sL($key));
        }

        return '';
    }

    public function renderListHeader($table, $currentIdList, $headerColumns, &$parentObject)
    {
        if (TCAService::getEnableColumn($table)) {
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            $buttonBar = GeneralUtility::makeInstance(ButtonBar::class);

            // Collect buttons
            foreach (CountryService::getAllCountries() ?: [] as $country) {
                $active = (int)$country['uid'] === $this->getCountryParameter();
                $url = $uriBuilder->buildUriFromRoute('web_list', [
                    'table' => $table,
                    'id' => $parentObject->id,
                    self::PARAMETER => $active ? 0 : $country['uid']
                ]);

                $buttonBar->addButton($buttonBar->makeLinkButton()
                    ->setHref($url)
                    ->setTitle($country['title'] ?? '')
                    ->setShowLabelText($active)
                    ->setIcon(IconService::getCountryIcon($country, null, $active ? 'overlay-readonly' : '')), null, $active ? 1 : 2);
            }

            // Render button bar
            foreach ($buttonBar->getButtons() as $groups) {
                $headerColumns['_CONTROL_'] .= $this->translate('LLL:EXT:z7_countries/Resources/Private/Language/locallang_db.xlf:tx_z7countries_country');

                foreach ($groups as $group) {
                    $headerColumns['_CONTROL_'] .= ' ' . implode('', $group);
                }
            }
        }

        return $headerColumns;
    }

    public function modifyQuery(array $parameters, string $table, int $pageId, array $additionalConstraints, array $fieldList, QueryBuilder &$queryBuilder): void
    {
        if (
            ($countryId = $this->getCountryParameter())
            && ($setup = TCAService::getEnableColumn($table))
            && ($country = CountryService::getCountryByUid($countryId))
        ) {
            $expression = CountryQueryRestriction::getExpression($queryBuilder->expr(), $setup['mode'], $setup['list'], $country);
            $queryBuilder->andWhere($expression);
        }
    }

    public function makeClip($table, $row, $cells, &$parentObject)
    {
        return $cells;
    }

    public function makeControl($table, $row, $cells, &$parentObject)
    {
        return $cells;
    }

    public function renderListHeaderActions($table, $currentIdList, $cells, &$parentObject)
    {
        return $cells;
    }
}
