<?php

declare(strict_types=1);

namespace Zeroseven\Countries\DataProcessing;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Zeroseven\Countries\Menu\LanguageMenu;
use Zeroseven\Countries\Menu\MenuInterface;

class LanguageMenuProcessor extends AbstractMenuProcessor
{
    public function getMenu(int $pageId = null): MenuInterface
    {
        return GeneralUtility::makeInstance(LanguageMenu::class, $pageId);
    }
}
