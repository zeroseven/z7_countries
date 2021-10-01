<?php

declare(strict_types=1);

namespace Zeroseven\Countries\ExpressionLanguage;

use TYPO3\CMS\Core\ExpressionLanguage\AbstractProvider;
use Zeroseven\Countries\Service\CountryService;

/**
 * Example:
 *
 * # Define uid of contact page
 * settings.contactPageUid = 5
 *
 * # Overwrite page uid for italy
 * [country.getUid() === 2]
 * settings.contactPageUid = 8
 * [global]
 *
 * # Overwrite page uid for germany and austria
 * [country.getIsoCode() in ['DE', 'AT']]
 * settings.contactPageUid = 12
 * [global]
 */
class TypoScriptConditionProvider extends AbstractProvider
{
    public function __construct()
    {
        $this->expressionLanguageVariables = [
            'country' => CountryService::getCountryByUri()
        ];
    }
}
