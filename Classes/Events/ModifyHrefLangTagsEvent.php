<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Events;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Event\ModifyHrefLangTagsEvent as OriginalEvent;
use TYPO3\CMS\Seo\HrefLang\HrefLangGenerator;

class ModifyHrefLangTagsEvent
{
    public function __invoke(OriginalEvent $event): void
    {
        if ((int)$this->getTypoScriptFrontendController()->page['no_index'] === 1) {
            return;
        }

        $event->setHrefLangs([]);
    }

    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
