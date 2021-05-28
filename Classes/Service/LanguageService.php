<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Service;

use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use Zeroseven\Countries\Model\Country;

class LanguageService
{
    public const BASE_DELIMITER = '-';

    protected static function cleanString(string $string, bool $allowUppercase = null, int $maxLength = null): string
    {
        $string = preg_replace('/[^a-z]/i', '', $string);

        if (!$allowUppercase) {
            $string = strtolower($string);
        }

        if ($maxLength) {
            $string = substr($string, 0, $maxLength);
        }

        return $string;
    }

    public static function manipulateBase(SiteLanguage $language, Country $country = null): UriInterface
    {
        if ($country && ($isoCode = $country->getIsoCode())) {
            return $language->getBase()->withPath('/' . self::cleanString($language->getTwoLetterIsoCode(), false, 2) . self::BASE_DELIMITER . self::cleanString($isoCode, true, 3) . '/');
        }

        return $language->getBase();
    }

    public static function manipulateHreflang(SiteLanguage $language, Country $country = null): string
    {
        if ($country && ($isoCode = $country->getIsoCode())) {
            return self::cleanString($language->getTwoLetterIsoCode(), false, 2) . '-' . self::cleanString(strtoupper($isoCode), true);
        }

        return $language->getHreflang();
    }
}
