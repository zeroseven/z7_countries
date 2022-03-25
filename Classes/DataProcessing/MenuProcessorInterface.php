<?php

declare(strict_types=1);

namespace Zeroseven\Countries\DataProcessing;

use Zeroseven\Countries\Menu\MenuInterface;

interface MenuProcessorInterface
{
    public function getMenu(int $pageId = null): MenuInterface;
}
