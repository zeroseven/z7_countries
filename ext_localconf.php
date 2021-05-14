<?php

defined('TYPO3_MODE') || die('🐰');

$GLOBALS['TYPO3_CONF_VARS']['DB']['additionalQueryRestrictions'][\Zeroseven\Countries\Database\QueryRestriction\CountryQueryRestriction::class] = ['disabled' => false];
