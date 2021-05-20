<?php

defined('TYPO3_MODE') || die('Access denied.');

call_user_func(static function () {

    // Register overlay icon
    \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class)->registerIcon(
        'overlay-country-restriction',
        \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
        ['source' => 'EXT:z7_countries/Resources/Public/Image/overlay-country-restriction.png']
    );
});
