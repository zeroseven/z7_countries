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

abstract class AbstractItem
{
    /** @var SiteLanguage|Country */
    protected $object;

    /** @var string */
    protected $link;

    /** @var string */
    protected $hreflang;

    /** @var bool */
    protected $available;

    /** @var bool */
    protected $active;

    /** @var bool */
    protected $current;

    /** @return SiteLanguage|Country */
    public function getObject()
    {
        return $this->object;
    }

    public function getData(): ?array
    {
        return method_exists($this->object, 'toArray') ? $this->object->toArray() : [];
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getHreflang(): string
    {
        return $this->hreflang;
    }

    public function setHreflang(string $hreflang): self
    {
        $this->hreflang = $hreflang;

        return $this;
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    public function setAvailable(bool $available): self
    {
        $this->available = $available;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function isCurrent(): bool
    {
        return $this->current;
    }

    public function setCurrent(bool $current): self
    {
        $this->current = $current;

        return $this;
    }

    /**
     * Pass methods to the object
     *
     * @param $action
     * @param $arguments
     */
    public function __call($action, $arguments)
    {
        if (is_callable([$this->object, $action]) && preg_match('/^(get|has|is)/', $action)) {
            return $this->getObject()->$action();
        }
    }
}

class LanguageItem extends AbstractItem
{
    /** @var array */
    protected $countries;

    public static function makeInstance(SiteLanguage $language, Country $country = null): self
    {
        return GeneralUtility::makeInstance(self::class)->setHreflang(LanguageManipulationService::getHreflang($language, $country))->setLanguage($language);
    }

    public function getLanguage(): SiteLanguage
    {
        return $this->object;
    }

    public function setLanguage(SiteLanguage $language): self
    {
        $this->object = $language;

        return $this;
    }

    public function getCountries(): array
    {
        return (array)$this->countries;
    }

    public function hasCountry($country): bool
    {
        if ($country instanceof CountryItem || $country instanceof Country) {
            return isset($this->countries[$country->getUid()]);
        }

        throw new \Exception('Value musst be type of CountryItem or Country', 1647335434);
    }

    public function addCountryItem(CountryItem $countryItem): self
    {
        if (!$this->hasCountry($countryItem)) {
            $this->countries[$countryItem->getUid()] = $countryItem;
        }

        return $this;
    }
}

class CountryItem extends AbstractItem
{
    /** @var array */
    protected $languages;

    public static function makeInstance(SiteLanguage $language, Country $country): self
    {
        return GeneralUtility::makeInstance(self::class)->setHreflang(LanguageManipulationService::getHreflang($language, $country))->setCountry($country);
    }

    public function getCountry(): Country
    {
        return $this->object;
    }

    public function setCountry(Country $country): self
    {
        $this->object = $country;

        return $this;
    }

    public function getLanguages(): array
    {
        return (array)$this->languages;
    }

    public function hasLanguage($language): bool
    {
        if ($language instanceof LanguageItem || $language instanceof SiteLanguage) {
            return isset($this->languages[$language->getLanguageId()]);
        }

        throw new \Exception('Value musst be type of LanguageItem or SiteLanguage', 1647335435);
    }

    public function addLanguageItem(LanguageItem $languageItem): self
    {
        if (!$this->hasLanguage($languageItem)) {
            $this->languages[$languageItem->getLanguageId()] = $languageItem;
        }

        return $this;
    }
}

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

    protected function getCountryMenuItem(SiteLanguage $language, Country $country): CountryItem
    {
        $link = $this->createLink($language, $country);
        $available = $link && $this->isAvailableCountry($country);

        return CountryItem::makeInstance($language, $country)
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

        return LanguageItem::makeInstance($language, $country)
            ->setLink((string)($countryAvailable ? $this->createLink($language, $country) : ''))
            ->setAvailable($available)
            ->setActive($available && $this->isActiveLanguage($language))
            ->setCurrent($available && $this->isActiveCountry($country) && $this->isActiveLanguage($language));
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
                    $menu[$country->getUid()] = $this->getCountryMenuItem($language, $country);
                }

                if (!$menu[$country->getUid()]->hasLanguage($language)) {
                    $menu[$country->getUid()]->addLanguageItem($this->getLanguageMenuItem($language, $country, $menu[$country->getUid()]->isAvailable()));
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
                $menu[$language->getLanguageId()]->addCountryItem($this->getCountryMenuItem($language, $country));
            }
        }

        return $menu;
    }
}
