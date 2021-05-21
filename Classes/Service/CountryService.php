<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Service;

use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Zeroseven\Countries\Database\QueryRestriction\CountryQueryRestriction;

class CountryService
{
    public const DELIMITER = '-';

    public static function getCountries(): array
    {
        // Return from "cache"
        if (isset($GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['cache']['countries'])) {
            return $GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['cache']['countries'];
        }

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_z7countries_country');
        $queryBuilder->getRestrictions()->removeByType(CountryQueryRestriction::class);

        return $GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['cache']['countries'] = (array)$queryBuilder->select('*')
            ->from('tx_z7countries_country')
            ->execute()
            ->fetchAllAssociative();
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

    public static function getCountryByUri(UriInterface $uri = null): ?array
    {
        $path = ($uri ?: new Uri((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . ':// . ' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']))->getPath();

        return
            preg_match('/^\/?[a-z]+' . self::DELIMITER . '([a-z]+)/i', $path, $matches)
            && ($countryIsoCode = $matches[1])
            && ($country = self::getCountryByIsoCode($countryIsoCode)) ? $country : null;
    }
}
