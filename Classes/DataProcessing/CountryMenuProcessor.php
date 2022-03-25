<?php

declare(strict_types=1);

namespace Zeroseven\Countries\DataProcessing;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Zeroseven\Countries\Menu\CountryMenu;
use Zeroseven\Countries\Menu\MenuInterface;

class CountryMenuProcessor extends AbstractMenuProcessor
{
    public function getMenu(int $pageId = null): MenuInterface
    {
        return GeneralUtility::makeInstance(CountryMenu::class, $pageId);
    }
}
