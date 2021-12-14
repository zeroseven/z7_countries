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
            if($language['available'] && $link = $language['link']) {
                if($language['object']->getLanguageId() === 0) {
                    $hreflang['x-default'] = $link;
                }

                $hreflang[$language['hreflang']] = $link;
            }

            foreach ($language['countries'] ?? [] as $country) {
                if($country['available'] && $link = $country['link']) {
                    $hreflang[$country['hreflang']] = $link;
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
