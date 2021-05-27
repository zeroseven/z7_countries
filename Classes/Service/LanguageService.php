<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Service;

use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use Zeroseven\Countries\Model\Country;

class LanguageService
{
    public static function createBase(SiteLanguage $language, Country $country = null): UriInterface
    {
        if ($country && ($isoCode = $country->getIsoCode())) {
            return $language->getBase()->withPath('/' . $language->getTwoLetterIsoCode() . CountryService::DELIMITER . $isoCode . '/');
        }

        return $language->getBase();
    }

    public static function createHreflang(SiteLanguage $language, Country $country = null): string
    {
        if ($country && ($isoCode = $country->getIsoCode())) {
            return $language->getTwoLetterIsoCode() . '-' . strtoupper($isoCode);
        }

        return $language->getHreflang();
    }
}
