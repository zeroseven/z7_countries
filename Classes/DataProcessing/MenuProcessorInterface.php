<?php

declare(strict_types=1);

namespace Zeroseven\Countries\DataProcessing;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

interface MenuProcessorInterface
{
    public function renderMenu(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData): array;
}
