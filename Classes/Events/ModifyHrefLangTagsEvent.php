<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Events;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Event\ModifyHrefLangTagsEvent as OriginalEvent;
use Zeroseven\Countries\Service\CountryService;
use Zeroseven\Countries\Service\LanguageService;

class ModifyHrefLangTagsEvent
{
    public function __invoke(OriginalEvent $event): void
    {
        if ((int)$this->getTypoScriptFrontendController()->page['no_index'] === 1) {
            return;
        }

        $context = GeneralUtility::makeInstance(Context::class);
        $originalLanguages = $context->getPropertyFromAspect('country', 'originalLanguages');
        $countries = CountryService::getCountries();

        $hreflangTags = [];

        foreach ($originalLanguages as $language) {
            $hreflangTags[$language->getHreflang()] = (string)$language->getBase();

            foreach ($countries as $country) {
                $countryUid = (int)$country['uid'];
                $hreflangTags[LanguageService::createHreflang($language, $countryUid)] = (string)LanguageService::createBase($language, $countryUid);
            }
        }

        $event->setHrefLangs($hreflangTags);
    }

    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
