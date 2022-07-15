<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Event\Listener;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Event\ModifyHrefLangTagsEvent as OriginalEvent;
use Zeroseven\Countries\Menu\LanguageMenu;

class ModifyHrefLangTagsEvent
{
    public function __invoke(OriginalEvent $event): void
    {
        if ((int)$this->getTypoScriptFrontendController()->page['no_index'] === 1) {
            return;
        }

        $hreflang = [];

        foreach (GeneralUtility::makeInstance(LanguageMenu::class)->render() as $languageItem) {
            if ($languageItem->isAvailable()) {
                if ($languageItem->getLanguageId() === 0) {
                    $hreflang['x-default'] = $languageItem->getLink();
                }

                $hreflang[$languageItem->getHreflang()] = $languageItem->getLink();
            }

            foreach ($languageItem->getCountries() as $countryItem) {
                if ($countryItem->isAvailable()) {
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
