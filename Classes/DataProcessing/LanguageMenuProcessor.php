<?php

declare(strict_types=1);

namespace Zeroseven\Countries\DataProcessing;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Zeroseven\Countries\Menu\AbstractMenu;
use Zeroseven\Countries\Menu\LanguageMenu;

class LanguageMenuProcessor extends AbstractMenuProcessor
{
    public function getMenu(int $pageId = null): AbstractMenu
    {
        return GeneralUtility::makeInstance(LanguageMenu::class, $pageId);
    }
}
