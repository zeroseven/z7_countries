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

        foreach ($menuUtility->getLanguageMenu() as $language) {
            if($language['available']) {
                if($language['object']->getLanguageId() === 0) {
                    $hreflang['x-default'] = $language['link'];
                }

                $hreflang[$language['hreflang']] = $language['link'];
            }

            foreach ($language['countries'] ?? [] as $country) {
                if($country['available']) {
                    $hreflang[$country['hreflang']] = $country['link'];
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
