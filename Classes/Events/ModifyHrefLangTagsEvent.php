<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Events;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Event\ModifyHrefLangTagsEvent as OriginalEvent;
use Zeroseven\Countries\Utility\MenuUtility;

class ModifyHrefLangTagsEvent
{
    public function __invoke(OriginalEvent $event): void
    {
        if ((int)$this->getTypoScriptFrontendController()->page['no_index'] === 1) {
            return;
        }

        $hreflang = [];
        $menuUtility = GeneralUtility::makeInstance(MenuUtility::class);

        foreach ($menuUtility->getLanguageMenu() as $languageItem) {
            if($languageItem->isAvailable()) {
                if($languageItem->getLanguage()->getLanguageId() === 0) {
                    $hreflang['x-default'] = $languageItem->getLink();
                }

                $hreflang[$languageItem->getHreflang()] = $languageItem->getLink();
            }

            foreach ($languageItem->getCountries() as $countryItem) {
                if($countryItem->isAvailable()) {
                    $hreflang[$countryItem->getHreflang()] = $countryItem->getLink();
                }
            }
        }

        $event->setHrefLangs($hreflang);
    }

    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
