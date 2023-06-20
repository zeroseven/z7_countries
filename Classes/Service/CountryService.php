<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Service;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use Zeroseven\Countries\Database\QueryRestriction\CountryQueryRestriction;
use Zeroseven\Countries\Model\Country;

class CountryService
{
    protected static function cacheObject($function, ...$arguments)
    {
        // Calculate key
        $key = md5(json_encode($arguments));

        // Return from "cache"
        if (array_key_exists($key, $GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['cache'])) {
            return $GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['cache'][$key];
        }

        // Create cache and return value
        return $GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['cache'][$key] = $function();
    }

    public static function getAllCountries(): array
    {
        /** @throws DBALException | Exception */
        $function = static function () {
            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_z7countries_country');
            $queryBuilder->getRestrictions()->removeByType(CountryQueryRestriction::class);

            return array_map(static function ($row) {
                return Country::makeInstance($row);
            }, $queryBuilder->select('*')->from('tx_z7countries_country')->execute()->fetchAllAssociative());
        };

        return self::cacheObject($function, 'allCountries');
    }

    public static function getCountriesByRecord(string $table, int $uid, array $row = null): ?array
    {
        $function = static function () use ($table, $uid, $row) {
            if (($modeColumn = TCAService::getModeColumn($table)) && $listColumn = TCAService::getListColumn($table)) {
                if (empty($row) || !isset($row[$modeColumn], $row[$listColumn])) {
                    $row = (array)BackendUtility::getRecord($table, $uid, $modeColumn . ',' . $listColumn);
                }

                if (isset($row[$listColumn]) && $row[$modeColumn] ?? null) {
                    if ($row[$listColumn] === '') {
                        return [];
                    }

                    return array_filter(array_map(static function ($uid) {
                        return self::getCountryByUid((int)$uid);
                    }, is_array($row[$listColumn]) ? $row[$listColumn] : GeneralUtility::intExplode(',', (string)$row[$listColumn])));
                }
            }

            return null;
        };

        return self::cacheObject($function, 'CountriesByRecord', $table, $uid, $row);
    }

    public static function getCountriesByLanguageUid(int $languageUid = null, Site $site = null): array
    {
        /** @throws SiteNotFoundException | AspectNotFoundException */
        $function = static function () use ($languageUid, $site) {
            if ($languageUid === null) {
                $context = GeneralUtility::makeInstance(Context::class);
                $languageUid = (int)$context->getPropertyFromAspect('language', 'id');
            }

            if($site === null && ($GLOBALS['TSFE'] ?? null) instanceof TypoScriptFrontendController && $uid = $GLOBALS['TSFE']->id) {
                $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($uid);
            }

            if ($site && ($siteConfiguration = $site->getConfiguration()) && $countries = $siteConfiguration['languages'][$languageUid]['countries'] ?? null) {
                return array_filter(array_map(static function ($uid) {
                    return self::getCountryByUid((int)$uid);
                }, is_string($countries) ? GeneralUtility::intExplode(',', $countries) : $countries));
            }

            return [];
        };

        return self::cacheObject($function, 'CountriesByLanguageUid', $languageUid, $site ? $site->getIdentifier() : null);
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
        $function = static function () use ($uri) {
            $path = ($uri ?: new Uri((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . ':// . ' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '')))->getPath();

            return
                preg_match('/^\/?[a-z]{2}' . LanguageManipulationService::BASE_DELIMITER . '([a-zA-Z0-9_-]+)/', $path, $matches)
                && ($countryParameter = $matches[1])
                && ($country = self::getCountryByParameter($countryParameter)) ? $country : null;
        };

        return self::cacheObject($function, 'CountryByUri', (string)$uri);
    }
}
