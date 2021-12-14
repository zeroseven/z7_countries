<?php

declare(strict_types=1);

namespace Zeroseven\Countries\DataProcessing;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

class LanguageMenuProcessor extends AbstractMenuProcessor implements DataProcessorInterface
{
    public function renderMenu(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData): array
    {
        return $this->menuUtility->getLanguageMenu();
    }
}
