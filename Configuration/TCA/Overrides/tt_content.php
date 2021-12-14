<?php

defined('TYPO3') || die('✌️');

call_user_func(static function () {

    // Add country selection to table "tt_content"
    \Zeroseven\Countries\Service\BackendService::enableConfiguration('tt_content');
});
