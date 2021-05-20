<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Hooks;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Zeroseven\Countries\Service\CountryService;
use Zeroseven\Countries\Service\TCAService;

class IconFactoryHook
{
    public function postOverlayPriorityLookup(string $table, array $row, array $status, string $iconName = null): ?string
    {
        if (empty($iconName) && $fields = TCAService::getEnableColumn($table)) {
            if (!isset($row[$fields['mode']], $row[$fields['list']]) && $uid = $row['uid'] ?? null) {
                $row = BackendUtility::getRecord($table, $uid);
            }

            if ($row[$fields['mode']]) {
                $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
                $country = count($countries = GeneralUtility::intExplode(',', (string)$row[$fields['list']])) === 1
                    ? CountryService::getCountryByUid($countries[0])
                    : [];

                return $iconFactory->mapRecordTypeToIconIdentifier('tx_z7countries_country', (array)$country);
            }
        }

        return $iconName;
    }
}
