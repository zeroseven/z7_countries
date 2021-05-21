<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Service;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class IconService
{
    public static function getFlagIdentifier(string $table, int $uid, array $row = null): ?string
    {
        if ($fields = TCAService::getEnableColumn($table)) {
            if ($row === null || !isset($row[$fields['mode']], $row[$fields['list']])) {
                $row = (array)BackendUtility::getRecord($table, $uid, implode(',', $fields));
            }

            if ($row[$fields['mode']]) {
                $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
                $country = count($countries = GeneralUtility::intExplode(',', (string)$row[$fields['list']])) === 1
                    ? CountryService::getCountryByUid($countries[0])
                    : [];

                return $iconFactory->mapRecordTypeToIconIdentifier('tx_z7countries_country', (array)$country);
            }
        }

        return null;
    }
}
