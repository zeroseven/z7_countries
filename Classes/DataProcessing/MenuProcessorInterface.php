<?php

declare(strict_types=1);

namespace Zeroseven\Countries\DataProcessing;

use Zeroseven\Countries\Menu\AbstractMenu;

interface MenuProcessorInterface
{
    public function getMenu(int $pageId = null): AbstractMenu;
}
