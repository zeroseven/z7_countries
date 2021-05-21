<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Hooks;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
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

    public function renderListHeader($table, $currentIdList, $headerColumns, &$parentObject)
    {
        if (TCAService::getEnableColumn($table)) {
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

            foreach (CountryService::getCountries() ?: [] as $country) {
                $title = $country['title'] ?? '';
                $icon = IconService::getCountryIcon($country) ?: $title;
                $url = $uriBuilder->buildUriFromRoute('web_list', [
                    'table' => $table,
                    'id' => $parentObject->id,
                    self::PARAMETER => (int)$country['uid'] === $this->getCountryParameter() ? 0 : $country['uid']
                ]);

                $headerColumns['_CONTROL_'] .= sprintf('<a href="%s" title="%s">%s</a>', $url, $title, $icon);
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
