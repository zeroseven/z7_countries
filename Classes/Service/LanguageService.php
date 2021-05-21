<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Service;

use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class LanguageService
{
    public static function createBase(SiteLanguage $language, int $countryUid): UriInterface
    {
        if ($country = CountryService::getCountryByUid($countryUid)) {
            return $language->getBase()->withPath('/' . $language->getTwoLetterIsoCode() . CountryService::DELIMITER . $country['iso_code'] . '/');
        }

        return $language->getBase();
    }

    public static function createHreflang(SiteLanguage $language, int $countryUid): string
    {
        if ($country = CountryService::getCountryByUid($countryUid)) {
            return $language->getTwoLetterIsoCode() . '-' . strtoupper($country['iso_code']);
        }

        return $language->getHreflang();
    }
}
