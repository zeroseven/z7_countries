<?php

declare(strict_types=1);

namespace Zeroseven\Countries\DataProcessing;

use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

abstract class AbstractMenuProcessor implements DataProcessorInterface, MenuProcessorInterface
{
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData): array
    {
        if (isset($processorConfiguration['if.']) && !$cObj->checkIf($processorConfiguration['if.'])) {
            return $processedData;
        }

        $pageId = ($id = $cObj->stdWrapValue('pageUid', $processorConfiguration ?? [])) && MathUtility::canBeInterpretedAsInteger($id) ? (int)$id : null;
        $processedData[$processorConfiguration['as'] ?? 'menu'] = $this->getMenu($pageId)->render();

        return $processedData;
    }
}
