<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Service;

use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
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

    public static function getCountriesByRecord(string $table, int $uid, array $row = null): ?array
    {
        if (($modeColumn = TCAService::getModeColumn($table)) && $listColumn = TCAService::getListColumn($table)) {
            if (empty($row) || !isset($row[$modeColumn], $row[$listColumn])) {
                $row = (array)BackendUtility::getRecord($table, $uid, $modeColumn . ',' . $listColumn);
            }

            if ($row[$modeColumn]) {
                if ($row[$listColumn] === '') {
                    return [];
                }

                return array_filter(array_map(static function ($uid) {
                    return self::getCountryByUid($uid);
                }, GeneralUtility::intExplode(',', (string)$row[$listColumn])));
            }
        }

        return null;
    }

    public static function getCountriesByLanguageUid(int $languageUid = null, Site $site = null): array
    {
        if ($languageUid === null) {
            $context = GeneralUtility::makeInstance(Context::class);
            $languageUid = (int)$context->getPropertyFromAspect('language', 'id');
        }

        $siteConfiguration = ($site ?: GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($GLOBALS['TSFE']->id))->getConfiguration();

        if ($countries = $siteConfiguration['languages'][$languageUid]['countries'] ?? null) {
            return array_filter(array_map(static function ($uid) {
                return self::getCountryByUid((int)$uid);
            }, is_string($countries) ? GeneralUtility::intExplode(',', $countries) : $countries));
        }

        return [];
    }

    public static function getCountryByParameter(string $countryParameter): ?Country
    {
        foreach (self::getAllCountries() as $country) {
            if ($country->getParameter() === $countryParameter) {
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
            preg_match('/^\/?[a-z]{2}' . LanguageManipulationService::BASE_DELIMITER . '([a-zA-Z0-9_-]+)/', $path, $matches)
            && ($countryParameter = $matches[1])
            && ($country = self::getCountryByParameter($countryParameter)) ? $country : null;
    }
}
