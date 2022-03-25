<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Menu;

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
use Zeroseven\Countries\Menu\Item\CountryItem;
use Zeroseven\Countries\Menu\Item\LanguageItem;
use Zeroseven\Countries\Model\Country;
use Zeroseven\Countries\Service\CountryService;
use Zeroseven\Countries\Service\LanguageManipulationService;
use Zeroseven\Countries\Service\TCAService;

abstract class AbstractMenu implements MenuInterface
{
    protected const TABLE_NAME = 'pages';

    protected int $pageId;

    protected Site $site;

    protected int $activeLanguageId;

    protected ?Country $activeCountry;

    protected UriBuilder $uriBuilder;

    protected QueryBuilder $queryBuilder;

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
        $uriBuilder = $this->uriBuilder->reset()
            ->setTargetPageUid($this->pageId)
            ->setCreateAbsoluteUri(false)
            ->setLanguage((string)$language->getLanguageId());

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
        return $country === null ? $this->activeCountry === null : $this->activeCountry && $this->activeCountry->getUid() === $country->getUid();
    }

    protected function isActiveLanguage(SiteLanguage $language = null): bool
    {
        return $language !== null && $language->getLanguageId() === $this->activeLanguageId;
    }

    protected function getCountryMenuItem(SiteLanguage $language, Country $country): CountryItem
    {
        $link = $this->createLink($language, $country);
        $available = $link && $this->isAvailableCountry($country);

        return GeneralUtility::makeInstance(CountryItem::class, $language, $country)
            ->setLink((string)$link)
            ->setAvailable($available)
            ->setActive($available && $this->isActiveCountry($country))
            ->setCurrent($available && $this->isActiveCountry($country) && $this->isActiveLanguage($language));
    }

    protected function getLanguageMenuItem(SiteLanguage $language, Country $country = null, bool $countryAvailable = null): LanguageItem
    {
        if ($countryAvailable === null) {
            $countryAvailable = $this->isAvailableCountry($country);
        }

        $available = $countryAvailable && $this->isAvailableLanguage($language);

        return GeneralUtility::makeInstance(LanguageItem::class, $language, $country)
            ->setLink((string)($countryAvailable ? $this->createLink($language, $country) : ''))
            ->setAvailable($available)
            ->setActive($available && $this->isActiveLanguage($language))
            ->setCurrent($available && $this->isActiveCountry($country) && $this->isActiveLanguage($language));
    }
}
