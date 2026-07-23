<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Service;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Psr\Http\Message\ServerRequestInterface;
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
            }, $queryBuilder->select('*')->from('tx_z7countries_country')->executeQuery()->fetchAllAssociative());
        };

        return self::cacheObject($function, 'allCountries');
    }

    public static function getCountriesByRecord(string $table, int $uid, ?array $row = null): ?array
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

    public static function isRecordAvailableForCountry(string $table, array $row, ?Country $country): bool
    {
        $enableColumns = TCAService::getEnableColumns($table);
        if ($enableColumns === null) {
            return true;
        }

        $mode = (int)($row[$enableColumns['mode']] ?? 0);
        if ($mode === 0) {
            return true;
        }
        if ($country === null) {
            return $mode === 2;
        }

        $countryUids = GeneralUtility::intExplode(',', (string)($row[$enableColumns['list']] ?? ''), true);
        return in_array($country->getUid(), $countryUids, true);
    }

    public static function getCountriesByLanguageUid(?int $languageUid = null, ?Site $site = null): array
    {
        /** @throws SiteNotFoundException | AspectNotFoundException */
        $function = static function () use ($languageUid, $site) {
            if ($languageUid === null) {
                $context = GeneralUtility::makeInstance(Context::class);
                $languageUid = (int)$context->getPropertyFromAspect('language', 'id');
            }

            if (
                $site === null
                && ($request = $GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface
                && ($uid = $request->getAttribute('frontend.page.information')?->getId())
            ) {
                $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($uid);
            }

            if ($site && $siteConfiguration = $site->getConfiguration()) {
                foreach ($siteConfiguration['languages'] ?? [] as $language) {
                    if ($language['languageId'] === $languageUid && $countries = $language['countries'] ?? null) {
                        return array_filter(array_map(static function ($uid) {
                            return self::getCountryByUid((int)$uid);
                        }, is_string($countries) ? GeneralUtility::intExplode(',', $countries) : $countries));
                    }
                }
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

    public static function getCountryByUri(?UriInterface $uri = null): ?Country
    {
        $function = static function () use ($uri) {
            if ($uri === null) {
                if (($request = $GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface) {
                    $uri = $request->getUri();
                } elseif (!empty($_SERVER['HTTP_HOST'])) {
                    $uri = new Uri((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . ($_SERVER['REQUEST_URI'] ?? ''));
                } else {
                    // No request context available (e.g. CLI)
                    return null;
                }
            }

            $path = $uri->getPath();

            return
                preg_match('/^\/?[a-z]{2}' . LanguageManipulationService::BASE_DELIMITER . '([a-zA-Z0-9_-]+)/', $path, $matches)
                && ($countryParameter = $matches[1])
                && ($country = self::getCountryByParameter($countryParameter)) ? $country : null;
        };

        return self::cacheObject($function, 'CountryByUri', (string)$uri);
    }
}
