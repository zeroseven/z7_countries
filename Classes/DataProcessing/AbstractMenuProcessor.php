<?php

declare(strict_types=1);

namespace Zeroseven\Countries\DataProcessing;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use Zeroseven\Countries\Utility\MenuUtility;

abstract class AbstractMenuProcessor implements DataProcessorInterface, MenuProcessorInterface
{
    protected MenuUtility $menuUtility;

    public function __construct()
    {
        $this->menuUtility = GeneralUtility::makeInstance(MenuUtility::class);
    }

    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData): array
    {
        if (isset($processorConfiguration['if.']) && !$cObj->checkIf($processorConfiguration['if.'])) {
            return $processedData;
        }

        if ($pageId = $cObj->stdWrapValue('pageUid', $processorConfiguration ?? [])) {
            $this->menuUtility->setPageId($pageId);
        }

        $processedData[$processorConfiguration['as'] ?? 'menu'] = $this->renderMenu($cObj, $contentObjectConfiguration, $processorConfiguration, $processedData);

        return $processedData;
    }
}
