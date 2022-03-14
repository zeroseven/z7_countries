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

    /** @var int */
    private $pageId;

    /** @var Site */
    private $site;

    /** @var int */
    private $activeLanguageId;

    /** @var Country */
    private $activeCountry;

    /** @var UriBuilder */
    private $uriBuilder;

    /** @var QueryBuilder */
    private $queryBuilder;

    public function __construct(int $pageId = null, Site $site = null)
    {
        $this->pageId = $pageId ?: (int)$this->getTypoScriptFrontendController()->id;

        $this->site = $site instanceof Site ? $site : GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($pageId ?: $this->pageId);

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

    protected function isAvailableLanguage(SiteLanguage $language): bool
    {
        $constraints = [];

        if ($language->getLanguageId()) {
            $constraints[] = $this->queryBuilder->expr()->andX(
                $this->queryBuilder->expr()->eq('l10n_parent', $this->pageId),
                $this->queryBuilder->expr()->eq('sys_language_uid', $language->getLanguageId())
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

    protected function createLink(SiteLanguage $language, Country $country = null): ?string
    {
        $uriBuilder = $this->uriBuilder->reset()->setTargetPageUid($this->pageId)->setCreateAbsoluteUri(false);

        if (!empty($language)) {
            $uriBuilder->setLanguage((string)$language->getLanguageId());
        }

        if ($url = $uriBuilder->build()) {
            if (($path = parse_url($url, PHP_URL_PATH)) && ($languageBase = $language->getBase()->getPath()) && strpos($path, $languageBase) === 0) {
                return LanguageManipulationService::getBase($language, $country) . substr($path, strlen($languageBase));
            }

            return $url;
        }

        return null;
    }

    protected function isActiveCountry(Country $country = null): bool
    {
        return empty($country) ? empty($this->activeCountry) : $this->activeCountry && $this->activeCountry->getUid() === $country->getUid();
    }

    protected function isActiveLanguage(SiteLanguage $language = null): bool
    {
        return !empty($language) && $language->getLanguageId() === $this->activeLanguageId;
    }

    protected function getCountryMenuItem(Country $country, SiteLanguage $language = null): array
    {
        $link = $this->createLink($language, $country);
        $available = $link && $this->isAvailableCountry($country);

        return [
            'data' => $country->toArray(),
            'object' => $country,
            'link' => $link,
            'hreflang' => LanguageManipulationService::getHreflang($language, $country),
            'available' => $available,
            'active' => $available && $this->isActiveCountry($country),
            'current' => $available && $this->isActiveCountry($country) && $this->isActiveLanguage($language)
        ];
    }

    protected function getLanguageMenuItem(SiteLanguage $language, Country $country = null, bool $countryAvailable = null): array
    {
        if ($countryAvailable === null) {
            $countryAvailable = $this->isAvailableCountry($country);
        }

        $available = $countryAvailable && $this->isAvailableLanguage($language);

        return [
            'data' => $language->toArray(),
            'object' => $language,
            'link' => $countryAvailable ? $this->createLink($language, $country) : null,
            'hreflang' => LanguageManipulationService::getHreflang($language, $country),
            'available' => $available,
            'active' => $available && $this->isActiveLanguage($language),
            'current' => $available && $this->isActiveCountry($country) && $this->isActiveLanguage($language)
        ];
    }

    public function setPageId(int $pageId): self
    {
        $this->pageId = $pageId;

        return $this;
    }

    public function getCountryMenu(): array
    {
        $menu = [];

        foreach ($this->site->getLanguages() as $language) {
            foreach (CountryService::getCountriesByLanguageUid($language->getLanguageId()) as $country) {
                if (!isset($menu[$country->getUid()])) {
                    $menu[$country->getUid()] = $this->getCountryMenuItem($country, $language);
                }

                if (!isset($menu[$country->getUid()]['languages'][$language->getLanguageId()])) {
                    $menu[$country->getUid()]['languages'][$language->getLanguageId()] = $this->getLanguageMenuItem($language, $country, $menu[$country->getUid()]['available']);
                }
            }
        }

        return $menu;
    }

    public function getLanguageMenu(): array
    {
        $menu = [];

        foreach ($this->site->getLanguages() as $language) {
            $menu[$language->getLanguageId()] = $this->getLanguageMenuItem($language);

            foreach (CountryService::getCountriesByLanguageUid($language->getLanguageId()) as $country) {
                $menu[$language->getLanguageId()]['countries'][$country->getUid()] = $this->getCountryMenuItem($country, $language);
            }
        }

        return $menu;
    }
}
