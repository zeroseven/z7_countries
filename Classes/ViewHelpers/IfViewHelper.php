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
            $type = 'string';

            if ($value['config']['type'] === 'check') {
                $type = 'bool';
            }

            if (strpos($value['config']['eval'] ?? '', 'int') > -1) {
                $type = 'int';
            }

            $this->registerArgument(GeneralUtility::underscoredToLowerCamelCase($key), $type, 'If the country matches the "' . $key . '".', false);
        }
    }

    public static function verdict(array $arguments, RenderingContextInterface $renderingContext): bool
    {
        $countryData = ($country = self::getCountry()) ? $country->toArray() : null;
        $conditionalArguments = array_filter(array_diff_key($arguments, array_flip(['__thenClosure', '__elseClosures'])), static function($v) {
            return $v !== null;
        });


        if (count($conditionalArguments) !== 1) {
            throw new Exception('Please use exactly one condition in ' . __CLASS__, 4685421754);
        }

        if (isset($arguments['isset'])) {
            return empty($countryData) === !$arguments['isset'];
        }

        foreach ($conditionalArguments as $argument => $value) {
            if ($countryData[GeneralUtility::camelCaseToLowerCaseUnderscored($argument)] === Country::castValue($value, $argument)) {
                return true;
            }
        }

        return false;
    }

    protected static function getCountry(): ?Country
    {
        // Cache country in case that there are many viewHelpers on the page
        return $GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['cache']['conditionCountry']
            ?? ($GLOBALS['TYPO3_CONF_VARS']['USER']['z7_countries']['cache']['conditionCountry'] = CountryService::getCountryByUri());
    }
}
