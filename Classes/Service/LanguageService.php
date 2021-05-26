<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Service;

use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class LanguageService
{
    public static function createBase(SiteLanguage $language, array $country): UriInterface
    {
        if ($isoCode = $country['iso_code'] ?? null) {
            return $language->getBase()->withPath('/' . $language->getTwoLetterIsoCode() . CountryService::DELIMITER . $isoCode . '/');
        }

        return $language->getBase();
    }

    public static function createHreflang(SiteLanguage $language, array $country): string
    {
        if ($isoCode = $country['iso_code'] ?? null) {
            return $language->getTwoLetterIsoCode() . '-' . strtoupper($isoCode);
        }

        return $language->getHreflang();
    }
}
