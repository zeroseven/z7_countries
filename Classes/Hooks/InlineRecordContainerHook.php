<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Hooks;

use TYPO3\CMS\Backend\Form\Element\InlineElementHookInterface;
use Zeroseven\Countries\Service\IconService;

class InlineRecordContainerHook implements InlineElementHookInterface, HookInterface
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

    public static function register(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms_inline.php']['tceformsInlineHook'][self::class] = self::class;
    }
}
