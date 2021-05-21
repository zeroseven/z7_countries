<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Hooks;

use TYPO3\CMS\Recordlist\RecordList\RecordListHookInterface;
use Zeroseven\Countries\Service\CountryService;
use Zeroseven\Countries\Service\IconService;
use Zeroseven\Countries\Service\TCAService;

class DatabaseRecordList implements RecordListHookInterface
{

    public function makeClip($table, $row, $cells, &$parentObject)
    {
        return $cells;
    }

    public function makeControl($table, $row, $cells, &$parentObject)
    {
        return $cells;
    }

    public function renderListHeader($table, $currentIdList, $headerColumns, &$parentObject)
    {
        if (TCAService::getEnableColumn($table)) {
            foreach (CountryService::getCountries() ?: [] as $country) {
                $headerColumns['_CONTROL_'] .= IconService::getCountryIcon($country);
            }
        }

        return $headerColumns;
    }

    public function renderListHeaderActions($table, $currentIdList, $cells, &$parentObject)
    {
        return $cells;
    }
}
