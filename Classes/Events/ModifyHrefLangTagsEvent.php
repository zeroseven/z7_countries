<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Events;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Event\ModifyHrefLangTagsEvent as OriginalEvent;
use Zeroseven\Countries\Database\QueryRestriction\CountryQueryRestriction;
use Zeroseven\Countries\Model\Country;
use Zeroseven\Countries\Service\CountryService;
use Zeroseven\Countries\Service\LanguageService;
use Zeroseven\Countries\Service\TCAService;

class ModifyHrefLangTagsEvent
{
    /** @var UriBuilder */
    protected $uriBuilder;

    /** @var QueryBuilder */
    protected $queryBuilder;

    /** @var string */
    protected $tableName;

    public function __construct()
    {
        $this->uriBuilder = GeneralUtility::makeInstance(ObjectManager::class)->get(UriBuilder::class);

        $this->tableName = 'pages';

        $this->queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->tableName);
        $this->queryBuilder->getRestrictions()->removeByType(CountryQueryRestriction::class);
    }

    protected function pageExists(SiteLanguage $language, Country $country = null): bool
    {
        $constraints = [];

        if ($languageId = $language->getLanguageId()) {
            $constraints[] = $this->queryBuilder->expr()->andX(
                $this->queryBuilder->expr()->eq('l10n_parent', $this->getTypoScriptFrontendController()->id),
                $this->queryBuilder->expr()->eq('sys_language_uid', $languageId)
            );
        } else {
            $constraints[] = $this->queryBuilder->expr()->eq('uid', $this->getTypoScriptFrontendController()->id);
        }

        if (TCAService::hasCountryConfiguration($this->tableName)) {
            $constraints[] = CountryQueryRestriction::getExpression($this->queryBuilder->expr(), $this->tableName, $country);
        }

        return (bool)$this->queryBuilder->count('uid')
            ->from($this->tableName)
            ->where($this->queryBuilder->expr()->andX(...$constraints))
            ->execute()
            ->fetchOne();
    }

    public function __invoke(OriginalEvent $event): void
    {
        if ((int)$this->getTypoScriptFrontendController()->page['no_index'] === 1) {
            return;
        }

        $context = GeneralUtility::makeInstance(Context::class);
        $originalLanguages = $context->getPropertyFromAspect('country', 'originalLanguages');
        $languages = $context->getPropertyFromAspect('country', 'manipulatedLanguages') ?: $originalLanguages;
        $hreflangs = $event->getHrefLangs();

        $hreflangTags = [];

        foreach ($languages as $key => $language) {
            if (($url = $hreflangs[$language->getHreflang()])) {
                $path = ltrim(str_replace((string)$language->getBase(), '', $url, $count), '/');

                if ($count) {
                    if ($this->pageExists($language)) {

                        // Get original language
                        $originalLanguage = $originalLanguages[$key];

                        // Set x-default to default language
                        if (!$language->getLanguageId()) {
                            $hreflangTags['x-default'] = $originalLanguage->getBase() . $path;
                        }

                        // Set hreflang of language (without country)
                        $hreflangTags[$originalLanguage->getHreflang()] = $originalLanguage->getBase() . $path;
                    }

                    // Set country variants of given language
                    foreach (CountryService::getCountriesByLanguageUid($language->getLanguageId()) as $country) {
                        if (!TCAService::hasCountryConfiguration($this->tableName) || $this->pageExists($language, $country)) {
                            $hreflangTags[LanguageService::manipulateHreflang($language, $country)] = LanguageService::manipulateBase($language, $country) . $path;
                        }
                    }
                }
            }
        }

        // Set new set of tags
        $event->setHrefLangs($hreflangTags);
    }

    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
