<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Event\Listener;

use TYPO3\CMS\Backend\Form\Event\ModifyFileReferenceControlsEvent as Event;
use Zeroseven\Countries\Service\IconService;

/**
 * Counterpart of ModifyInlineElementControlsEvent for TCA type=file fields,
 * which are rendered by FileReferenceContainer and therefore do not pass
 * through the inline element controls.
 */
class ModifyFileReferenceControlsEvent
{
    protected const TABLE = 'sys_file_reference';

    public function __invoke(Event $event): void
    {
        $uid = (int)($event->getRecord()['uid'] ?? 0);

        if ($uid && $icon = IconService::getRecordFlagIcon(self::TABLE, $uid)) {
            $controls = $event->getControls();
            $firstControlKey = array_key_first($controls);

            if ($firstControlKey === null) {
                return;
            }

            $controls[$firstControlKey] = '<span style="margin:5px 10px">' . $icon->render() . '</span>' . $controls[$firstControlKey];

            $event->setControls($controls);
        }
    }
}
