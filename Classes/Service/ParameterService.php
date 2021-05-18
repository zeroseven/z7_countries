<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Service;

use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ParameterService
{
    private const DELIMITER = '_';

    private static function createUri(): UriInterface
    {
        return new Uri((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . ':// . ' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    }

    public static function getCountryByIsoCode(string $countryIsoCode): ?array
    {
        foreach (self::getCountries() as $country) {
            if ($country['iso_code'] === $countryIsoCode) {
                return $country;
            }
        }

        return null;
    }

    public static function getCountryByUid(int $countryUid): ?array
    {
        foreach (self::getCountries() as $country) {
            if ((int)$country['uid'] === $countryUid) {
                return $country;
            }
        }

        return null;
    }

    public static function getCountries(): array
    {
        return (array)GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_z7countries_country')
            ->createQueryBuilder()
            ->select('*')
            ->from('tx_z7countries_country')
            ->execute()
            ->fetchAll();
    }

    public static function getCountry(UriInterface $uri = null): ?int
    {
        $path = ($uri ?: self::createUri())->getPath();

        return
            preg_match('/^\/?[a-z]+' . self::DELIMITER . '([a-z]+)/i', $path, $matches)
            && ($countryIsoCode = $matches[1])
            && ($country = self::getCountryByIsoCode($countryIsoCode)) ? $country['uid'] : null;
    }

    public static function hasCountry(UriInterface $uri = null): bool
    {
        return (bool)self::getCountry($uri);
    }

    public static function createLanguageBase(SiteLanguage $language, int $countryUid): UriInterface
    {
        if ($country = self::getCountryByUid($countryUid)) {
            return $language->getBase()->withPath($language->getTwoLetterIsoCode() . self::DELIMITER . $country['iso_code']);
        }

        return $language->getBase();
    }

    public static function createLanguageHreflang(SiteLanguage $language, int $countryUid): string
    {
        if ($country = self::getCountryByUid($countryUid)) {
            return $language->getTwoLetterIsoCode() . '_' . strtoupper($country['iso_code']);
        }

        return $language->getHreflang();
    }
}
