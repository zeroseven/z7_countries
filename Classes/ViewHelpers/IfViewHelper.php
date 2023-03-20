<?php

declare(strict_types=1);

namespace Zeroseven\Countries\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\ViewHelpers\Exception;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
use Zeroseven\Countries\Model\Country;
use Zeroseven\Countries\Service\CountryService;

class IfViewHelper extends AbstractConditionViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();

        $this->registerArgument('uid', 'int', 'Compare uid of the country.', false);
        $this->registerArgument('isset', 'bool', 'If a country is expected or not.', false);

        // Add dynamic arguments
        foreach ($GLOBALS['TCA']['tx_z7countries_country']['columns'] ?? [] as $key => $value) {
            $propertyName = GeneralUtility::underscoredToLowerCamelCase($key);
            $this->registerArgument($propertyName, 'mixed', 'If the country matches the "' . $propertyName . '".', false);
        }
    }

    public static function verdict(array $arguments, RenderingContextInterface $renderingContext): bool
    {
        $country = CountryService::getCountryByUri();
        $conditionalArguments = array_filter(array_diff_key($arguments, array_flip(['else', 'then', '__thenClosure', '__elseClosures'])), static function ($v) {
            return $v !== null;
        });

        if (count($conditionalArguments) !== 1) {
            throw new Exception('Please use exactly one condition in ' . __CLASS__, 1633110558);
        }

        if (isset($arguments['isset'])) {
            return empty($country) === !$arguments['isset'];
        }

        if (empty($country)) {
            return false;
        }

        foreach ($conditionalArguments as $argument => $value) {
            if ($country->getProperty($argument) === Country::castValue($value, $argument)) {
                return true;
            }
        }

        return false;
    }
}
