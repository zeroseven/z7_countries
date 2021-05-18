<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class ParameterService
{
    public const COUNTRY_PARAMETER = 'tx_z7country';

    public static function getCountry(): ?int
    {

        // Todo: Get country parameter from routeEnhancers also …
        if (($parameter = GeneralUtility::_GP(self::COUNTRY_PARAMETER)) && MathUtility::canBeInterpretedAsInteger($parameter)) {
            return (int)$parameter;
        }

        return null;
    }
}
