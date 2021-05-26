<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Service;

use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Zeroseven\Countries\Database\QueryRestriction\CountryQueryRestriction;
use Zeroseven\Countries\Model\Country;

class CountryService
{
    public const DELIMITER = '-';

    public static function getAllCountries(): array
    {
        // Return from "cache"
        if (isset($GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['cache']['countries'])) {
            return $GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['cache']['countries'];
        }

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_z7countries_country');
        $queryBuilder->getRestrictions()->removeByType(CountryQueryRestriction::class);

        return $GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['cache']['countries'] = array_map(static function ($row) {
            return Country::makeInstance($row);
        }, (array)$queryBuilder->select('*')->from('tx_z7countries_country')->execute()->fetchAllAssociative());
    }

    public static function getCountriesByLanguageUid(int $languageUid = null, Site $site = null): array
    {
        if ($languageUid === null) {
            $context = GeneralUtility::makeInstance(Context::class);
            $languageUid = (int)$context->getPropertyFromAspect('language', 'id');
        }

        $siteConfiguration = ($site ?: GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($GLOBALS['TSFE']->id))->getConfiguration();

        if ($siteLanguage = $siteConfiguration['languages'][$languageUid] ?? null) {
            return array_map(static function ($uid) {
                return self::getCountryByUid($uid);
            }, (array)GeneralUtility::intExplode(',', $siteLanguage['countries']));
        }

        return [];
    }

    public static function getCountryByIsoCode(string $countryIsoCode): ?Country
    {
        foreach (self::getAllCountries() as $country) {
            if ($country->getIsoCode() === $countryIsoCode) {
                return $country;
            }
        }

        return null;
    }

    public static function getCountryByUid(int $countryUid): ?Country
    {
        foreach (self::getAllCountries() as $country) {
            if ($country->getUid() === $countryUid) {
                return $country;
            }
        }

        return null;
    }

    public static function getCountryByUri(UriInterface $uri = null): ?Country
    {
        $path = ($uri ?: new Uri((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . ':// . ' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']))->getPath();

        return
            preg_match('/^\/?[a-z]+' . self::DELIMITER . '([a-z]+)/i', $path, $matches)
            && ($countryIsoCode = $matches[1])
            && ($country = self::getCountryByIsoCode($countryIsoCode)) ? $country : null;
    }
}
