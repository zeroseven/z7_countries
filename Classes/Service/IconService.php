<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Service;

use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Zeroseven\Countries\Model\Country;

class IconService
{
    protected const TABLE = 'tx_z7countries_country';

    public static function getCountryIdentifier(Country $country): ?string
    {
        $countryArray = $country->toArray();

        if (($iconColumn = $GLOBALS['TCA'][self::TABLE]['ctrl']['typeicon_column'] ?? null) && $countryArray[$iconColumn] ?? null) {
            if (
                ($mask = $GLOBALS['TCA'][self::TABLE]['ctrl']['typeicon_classes']['mask'] ?? null)
                && ($icon = str_replace('###TYPE###', (string)$countryArray[$iconColumn], (string)$mask, $count))
                && $count
            ) {
                return $icon;
            }

            return $countryArray[$iconColumn];
        }

        if ($defaultIcon = $GLOBALS['TCA'][self::TABLE]['ctrl']['typeicon_classes']['default']) {
            return $defaultIcon;
        }

        return null;
    }

    public static function getCountryIcon(Country $country, $size = null, $overlayIdentifier = null): ?Icon
    {
        if ($identifier = self::getCountryIdentifier($country)) {
            return GeneralUtility::makeInstance(IconFactory::class)->getIcon($identifier, $size ?: Icon::SIZE_SMALL, $overlayIdentifier);
        }

        return null;
    }

    public static function getRecordFlagIdentifier(string $table, int $uid, array $row = null): ?string
    {
        if (is_array($countries = CountryService::getCountriesByRecord($table, $uid, $row))) {
            $data = count($countries) === 1 && $countries[0] ? $countries[0]->toArray() : [];

            return GeneralUtility::makeInstance(IconFactory::class)->mapRecordTypeToIconIdentifier(self::TABLE, $data);
        }

        return null;
    }
}
