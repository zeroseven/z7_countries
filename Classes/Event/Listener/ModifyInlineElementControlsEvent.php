<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Event\Listener;

use TYPO3\CMS\Backend\Form\Event\ModifyInlineElementControlsEvent as Event;
use Zeroseven\Countries\Service\IconService;

class ModifyInlineElementControlsEvent
{
    public function __invoke(Event $event)
    {
        $uid = (int)($event->getRecord()['uid'] ?? 0);
        $table = $event->getElementData()['tableName'] ?? null;

        if ($uid && $table && $icon = IconService::getRecordFlagIcon($table, $uid)) {
            $controls = $event->getControls();
            $firstControlKey = array_key_first($controls);

            $controls[$firstControlKey] = '<span style="margin:5px 10px">' . $icon->render() . '</span>' . $controls[$firstControlKey];

            $event->setControls($controls);
        }
    }
}
