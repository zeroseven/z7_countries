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

    /** @var int */
    private $pageId;

    /** @var int */
    private $activeLanguageId;

    /** @var Site */
    private $site;

    /** @var Country */
    private $activeCountry;

    /** @var UriBuilder */
    private $uriBuilder;

    /** @var QueryBuilder */
    private $queryBuilder;

    public function __construct(int $pageId = null)
    {
        $this->pageId = $pageId ?: (int)$this->getTypoScriptFrontendController()->id;

        $this->activeLanguageId = (int)GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('language', 'id');

        $this->activeCountry = CountryService::getCountryByUri();

        $this->uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        $this->queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::TABLE_NAME);
        $this->queryBuilder->getRestrictions()->removeByType(CountryQueryRestriction::class);

        $this->setSite();
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
            $path = ltrim(str_replace($language->getBase()->getPath(), '', $url, $count), '/');

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

    protected function isActiveCountry(Country $country = null): bool
    {
        return empty($country) ? empty($this->activeCountry) : $this->activeCountry && $this->activeCountry->getUid() === $country->getUid();
    }

    protected function isActiveLanguage(int $languageId = null): bool
    {
        return $languageId !== null && $languageId === $this->activeLanguageId;
    }

    protected function getCountryMenuItem(Country $country = null, int $languageId = 0): array
    {
        return [
            'data' => $country === null ? [] : $country->toArray(),
            'link' => $this->createLink($languageId, $country),
            'available' => $this->isAvailableCountry($country),
            'active' => $this->isActiveCountry($country),
            'current' => $this->isActiveCountry($country) && $this->isActiveLanguage($languageId)
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
            'active' => $this->isActiveLanguage($languageId),
            'current' => $this->isActiveCountry($country) && $this->isActiveLanguage($languageId)
        ];
    }

    protected function getLanguageConfigurations(): array
    {
        $siteConfiguration = $this->site->getConfiguration();

        return $siteConfiguration['languages'];
    }

    protected function setSite(int $pageId = null): self
    {
        $this->site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($pageId ?: $this->pageId);

        return $this;
    }

    public function setPageId(int $pageId): self
    {
        $this->pageId = $pageId;

        return $this->setSite($pageId);
    }

    public function getCountryMenu(): array
    {
        $menu = [];

        foreach ($this->getLanguageConfigurations() as $languageConfiguration) {
            $languageId = (int)$languageConfiguration['languageId'];
            $countryRelations = is_string($languageConfiguration['countries']) ? GeneralUtility::intExplode(',', $languageConfiguration['countries']) : $languageConfiguration['countries'];

            foreach ($countryRelations as $countryUid) {
                $country = CountryService::getCountryByUid($countryUid);

                if (!isset($menu[$countryUid])) {
                    $menu[$countryUid] = $this->getCountryMenuItem($country, $this->activeLanguageId);
                }

                if (!isset($menu[$countryUid]['languages'][$languageId])) {
                    $menu[$countryUid]['languages'][$languageId] = $this->getLanguageMenuItem($languageConfiguration, $country, $menu[$countryUid]['available']);
                }
            }
        }

        return $menu;
    }

    public function getLanguageMenu(): array
    {
        $menu = [];

        foreach ($this->getLanguageConfigurations() as $languageConfiguration) {
            $languageId = (int)$languageConfiguration['languageId'];
            $countryRelations = is_string($languageConfiguration['countries']) ? GeneralUtility::intExplode(',', $languageConfiguration['countries']) : $languageConfiguration['countries'];

            $menu[$languageId] = $this->getLanguageMenuItem($languageConfiguration);

            foreach ($countryRelations as $countryUid) {
                $country = CountryService::getCountryByUid($countryUid);

                $menu[$languageId]['countries'][$countryUid] = $this->getCountryMenuItem($country, $languageId);
            }

        }

        return $menu;
    }

    public function getInternationalMenu(): array
    {
        $menu = $this->getCountryMenuItem();

        foreach ($this->getLanguageConfigurations() as $languageConfiguration) {
            $menu['languages'][$languageConfiguration['languageId']] = $this->getLanguageMenuItem($languageConfiguration);
        }

        return $menu;
    }
}
