<?php

defined('TYPO3_MODE') || die('✌️');

call_user_func(static function (string $table) {

    // Add country selection to table "tt_content"
    \Zeroseven\Countries\Utility\TCAUtility::add($table);

}, 'tt_content');
