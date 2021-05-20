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

    /** @var array */
    protected $tableSetup;

    public function __construct()
    {
        $this->uriBuilder = GeneralUtility::makeInstance(ObjectManager::class)->get(UriBuilder::class);

        $this->tableName = 'pages';

        $this->queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->tableName);
        $this->queryBuilder->getRestrictions()->removeByType(CountryQueryRestriction::class);

        $this->tableSetup = TCAService::getEnableColumns($this->tableName);
    }

    protected function pageExists(SiteLanguage $language, array $country = null): bool
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

        if (!empty($this->tableSetup)) {
            $constraints[] = CountryQueryRestriction::getExpression($this->queryBuilder->expr(), $this->tableSetup['mode'], $this->tableSetup['list'], $country);
        }

        return (bool)$this->queryBuilder->count('uid')
            ->from($this->tableName)
            ->where($this->queryBuilder->expr()->andX(...$constraints))
            ->execute()
            ->fetchOne();
    }

    protected function createUrl(): string
    {
        $pageUid = (int)$this->getTypoScriptFrontendController()->id;

        return $this->uriBuilder->reset()->setTargetPageUid($pageUid)->build();
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
        $countries = CountryService::getCountries();

        $hreflangTags = [];

        foreach ($languages as $key => $language) {
            if (($url = $hreflangs[$language->getHreflang()])) {
                $path = ltrim(str_replace((string)$language->getBase(), '', $url, $count), '/');

                if ($count) {
                    if ($this->pageExists($language)) {
                        $originalLanguage = $originalLanguages[$key];
                        $hreflangTags[$originalLanguage->getHreflang()] = $originalLanguage->getBase() . $path;
                    }

                    foreach ($countries as $country) {
                        if (empty($this->tableSetup) || $this->pageExists($language, $country)) {
                            $countryUid = (int)$country['uid'];
                            $hreflangTags[LanguageService::createHreflang($language, $countryUid)] = LanguageService::createBase($language, $countryUid) . $path;
                        }
                    }
                }
            }
        }

        $event->setHrefLangs($hreflangTags);
    }

    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
