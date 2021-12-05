<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Utility;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use Zeroseven\Countries\Database\QueryRestriction\CountryQueryRestriction;
use Zeroseven\Countries\Model\Country;
use Zeroseven\Countries\Service\CountryService;
use Zeroseven\Countries\Service\LanguageManipulationService;
use Zeroseven\Countries\Service\TCAService;

class MenuUtility
{
    protected const TABLE_NAME = 'pages';

    /** @var Site */
    private $site;

    /** @var int */
    private $activeLanguageId;

    /** @var Country */
    private $activeCountry;

    /** @var QueryBuilder */
    private $queryBuilder;

    /** @var UriBuilder */
    private $uriBuilder;

    public function __construct()
    {
        $this->site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($GLOBALS['TSFE']->id);

        $this->activeLanguageId = (int)GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('language', 'id');

        $this->activeCountry = CountryService::getCountryByUri();

        $this->uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        $this->queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::TABLE_NAME);
        $this->queryBuilder->getRestrictions()->removeByType(CountryQueryRestriction::class);
    }

    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    protected function isAvailableLanguage(int $languageId, int $pageId): bool
    {
        $constraints = [];

        if ($languageId) {
            $constraints[] = $this->queryBuilder->expr()->andX(
                $this->queryBuilder->expr()->eq('l10n_parent', $pageId),
                $this->queryBuilder->expr()->eq('sys_language_uid', $languageId)
            );
        } else {
            $constraints[] = $this->queryBuilder->expr()->eq('uid', $pageId);
        }

        return (bool)$this->queryBuilder->count('uid')
            ->from(self::TABLE_NAME)
            ->where($this->queryBuilder->expr()->andX(...$constraints))
            ->execute()
            ->fetchOne();
    }

    protected function isAvailableCountry(Country $country, int $pageId): bool
    {
        $constraints = [$this->queryBuilder->expr()->eq('uid', $pageId)];

        if (TCAService::hasCountryConfiguration(self::TABLE_NAME)) {
            $constraints[] = CountryQueryRestriction::getExpression($this->queryBuilder->expr(), self::TABLE_NAME, $country);
        }

        return (bool)$this->queryBuilder->count('uid')
            ->from(self::TABLE_NAME)
            ->where($this->queryBuilder->expr()->andX(...$constraints))
            ->execute()
            ->fetchOne();
    }

    protected function createLink(Country $country, int $languageId, int $pageId): ?string
    {
        if ($url = $this->uriBuilder->reset()->setTargetPageUid($pageId)->setLanguage((string)$languageId)->build()) {
            $language = $this->site->getLanguageById($languageId);
            $path = ltrim(str_replace((string)$language->getBase()->getPath(), '', $url, $count), '/');

            if ($count) {
                return LanguageManipulationService::getBase($language, $country) . $path;
            }

            return $url;
        }

        return null;
    }

    public function getCountryMenu(): array
    {
        $menu = [];

        $pageId = $this->getTypoScriptFrontendController()->id;
        $siteConfiguration = $this->site->getConfiguration();

        foreach ($siteConfiguration['languages'] as $languageConfiguration) {
            $languageId = (int)$languageConfiguration['languageId'];
            $countryRelations = is_string($languageConfiguration['countries']) ? GeneralUtility::intExplode(',', $languageConfiguration['countries']) : $languageConfiguration['countries'];

            foreach ($countryRelations as $countryRelation) {
                $country = CountryService::getCountryByUid($countryRelation);
                $countryAvailable = $this->isAvailableCountry($country, $pageId);

                $menu[$countryRelation]['country'] = [
                    'data' => $country->toArray(),
                    'active' => $this->activeCountry && $this->activeCountry->getUid() === $country->getUid(),
                    'available' => $countryAvailable
                ];

                $menu[$countryRelation]['languages'][$languageId] = [
                    'data' => $languageConfiguration,
                    'link' => $countryAvailable ? $this->createLink($country, $languageId, $pageId) : null,
                    'available' => $countryAvailable && $this->isAvailableLanguage($languageId, $pageId),
                    'active' => $countryAvailable && $languageId === $this->activeLanguageId,
                ];
            }
        }

        return $menu;
    }

    public function getLanguageMenu(): array
    {
        return [];
    }
}
