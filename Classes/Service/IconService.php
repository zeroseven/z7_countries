<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Service;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class IconService
{
    protected const TABLE = 'tx_z7countries_country';

    public static function getCountryIdentifier(array $country): ?string
    {
        if (($iconColumn = $GLOBALS['TCA'][self::TABLE]['ctrl']['typeicon_column'] ?? null) && $country[$iconColumn] ?? null) {
            if (
                ($mask = $GLOBALS['TCA'][self::TABLE]['ctrl']['typeicon_classes']['mask'] ?? null)
                && ($icon = str_replace('###TYPE###', (string)$country[$iconColumn], (string)$mask, $count))
                && $count
            ) {
                return $icon;
            }

            return $country[$iconColumn];
        }

        if ($defaultIcon = $GLOBALS['TCA'][self::TABLE]['ctrl']['typeicon_classes']['default']) {
            return $defaultIcon;
        }

        return null;
    }

    public static function getCountryIcon(array $country, $size = null, $overlayIdentifier = null): ?string
    {
        if ($identifier = self::getCountryIdentifier($country)) {
            return (string)GeneralUtility::makeInstance(IconFactory::class)->getIcon($identifier, $size ?: Icon::SIZE_SMALL, $overlayIdentifier);
        }

        return null;
    }

    public static function getRecordFlagIdentifier(string $table, int $uid, array $row = null): ?string
    {
        if ($fields = TCAService::getEnableColumn($table)) {
            if ($row === null || !isset($row[$fields['mode']], $row[$fields['list']])) {
                $row = (array)BackendUtility::getRecord($table, $uid, implode(',', $fields));
            }

            if ($row[$fields['mode']]) {
                $country = count($countries = GeneralUtility::intExplode(',', (string)$row[$fields['list']])) === 1
                    ? CountryService::getCountryByUid($countries[0])
                    : [];

                return GeneralUtility::makeInstance(IconFactory::class)->mapRecordTypeToIconIdentifier(self::TABLE, (array)$country);
            }
        }

        return null;
    }
}
