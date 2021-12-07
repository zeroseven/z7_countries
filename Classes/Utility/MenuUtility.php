<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Utility;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
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

    /** @var int */
    private $pageId;

    public function __construct(int $pageId = null)
    {
        $this->site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($GLOBALS['TSFE']->id);

        $this->activeLanguageId = (int)GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('language', 'id');

        $this->activeCountry = CountryService::getCountryByUri();

        $this->uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        $this->queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::TABLE_NAME);
        $this->queryBuilder->getRestrictions()->removeByType(CountryQueryRestriction::class);

        $this->pageId = $pageId ?: (int)$this->getTypoScriptFrontendController()->id;
    }

    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    protected function isAvailableLanguage(int $languageId): bool
    {
        $constraints = [];

        if ($languageId) {
            $constraints[] = $this->queryBuilder->expr()->andX(
                $this->queryBuilder->expr()->eq('l10n_parent', $this->pageId),
                $this->queryBuilder->expr()->eq('sys_language_uid', $languageId)
            );
        } else {
            $constraints[] = $this->queryBuilder->expr()->eq('uid', $this->pageId);
        }

        return (bool)$this->queryBuilder->count('uid')
            ->from(self::TABLE_NAME)
            ->where($this->queryBuilder->expr()->andX(...$constraints))
            ->execute()
            ->fetchOne();
    }

    protected function isAvailableCountry(Country $country = null): bool
    {
        $constraints = [$this->queryBuilder->expr()->eq('uid', $this->pageId)];

        if (TCAService::hasCountryConfiguration(self::TABLE_NAME)) {
            $constraints[] = CountryQueryRestriction::getExpression($this->queryBuilder->expr(), self::TABLE_NAME, $country);
        }

        return (bool)$this->queryBuilder->count('uid')
            ->from(self::TABLE_NAME)
            ->where($this->queryBuilder->expr()->andX(...$constraints))
            ->execute()
            ->fetchOne();
    }

    protected function createLink(int $languageId, Country $country = null): ?string
    {
        if ($url = $this->uriBuilder->reset()->setTargetPageUid($this->pageId)->setLanguage((string)$languageId)->build()) {
            $language = $this->site->getLanguageById($languageId);
            $path = ltrim(str_replace((string)$language->getBase()->getPath(), '', $url, $count), '/');

            if ($count) {
                if(empty($country) && $originalLanguages = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('country', 'originalLanguages')) {
                    return $originalLanguages[$languageId]->getBase() . $path;
                }

                return LanguageManipulationService::getBase($language, $country) . $path;
            }

            return $url;
        }

        return null;
    }

    protected function getCountryMenuItem(Country $country = null): array
    {
        return [
            'data' => $country === null ? [] : $country->toArray(),
            'active' => empty($country) ? empty($this->activeCountry) : $this->activeCountry && $this->activeCountry->getUid() === $country->getUid(),
            'available' => $this->isAvailableCountry($country)
        ];
    }

    protected function getLanguageMenuItem(array $languageConfiguration, Country $country = null, bool $countryAvailable = null): array
    {
        if ($countryAvailable === null) {
            $countryAvailable = $this->isAvailableCountry($country);
        }

        $languageId = (int)$languageConfiguration['languageId'];

        return [
            'data' => $languageConfiguration,
            'link' => $countryAvailable ? $this->createLink($languageId, $country) : null,
            'available' => $countryAvailable && $this->isAvailableLanguage($languageId),
            'active' => $countryAvailable && $languageId === $this->activeLanguageId,
        ];
    }

    public function setPageId(int $pageId): self
    {
        $this->pageId = $pageId;

        return $this;
    }

    public function getCountryMenu(): array
    {
        $siteConfiguration = $this->site->getConfiguration();

        $menu = [];

        foreach ($siteConfiguration['languages'] as $languageConfiguration) {
            $languageId = (int)$languageConfiguration['languageId'];
            $countryRelations = is_string($languageConfiguration['countries']) ? GeneralUtility::intExplode(',', $languageConfiguration['countries']) : $languageConfiguration['countries'];

            foreach ($countryRelations as $countryRelation) {
                $country = CountryService::getCountryByUid($countryRelation);

                if (!isset($menu[$countryRelation])) {
                    $menu[$countryRelation] = $this->getCountryMenuItem($country);
                }

                if (!isset($menu[$countryRelation]['languages'][$languageId])) {
                    $menu[$countryRelation]['languages'][$languageId] = $this->getLanguageMenuItem($languageConfiguration, $country, $menu[$countryRelation]['available']);
                }
            }
        }

        return $menu;
    }

    public function getInternationalMenu(): array
    {
        $siteConfiguration = $this->site->getConfiguration();

        $menu = $this->getCountryMenuItem();

        foreach ($siteConfiguration['languages'] as $languageConfiguration) {
            $languageId = (int)$languageConfiguration['languageId'];
            $menu['languages'][$languageId] = $this->getLanguageMenuItem($languageConfiguration);
        }

        return $menu;
    }

    public function getLanguageMenu(): array
    {
        return [];
    }
}
