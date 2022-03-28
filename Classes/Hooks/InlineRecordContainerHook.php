<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Hooks;

use TYPO3\CMS\Backend\Form\Element\InlineElementHookInterface;
use Zeroseven\Countries\Service\IconService;

class InlineRecordContainerHook implements InlineElementHookInterface
{
    public function renderForeignRecordHeaderControl_preProcess($parentUid, $foreignTable, array $childRecord, array $childConfig, $isVirtual, array &$enabledControls)
    {

    }

    public function renderForeignRecordHeaderControl_postProcess($parentUid, $foreignTable, array $childRecord, array $childConfig, $isVirtual, array &$controlItems)
    {
        if ($icon = IconService::getRecordFlagIcon($foreignTable, (int)$childRecord['uid'])) {
            $controlItems['edit'] = '<span style="margin:5px 10px">' . $icon->render() . '</span>' . $controlItems['edit'];
        }
    }
}
