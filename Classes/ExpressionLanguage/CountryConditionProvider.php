<?php

declare(strict_types=1);

namespace Zeroseven\Countries\ExpressionLanguage;

use TYPO3\CMS\Core\ExpressionLanguage\AbstractProvider;
use Zeroseven\Countries\Service\CountryService;

class CountryConditionProvider extends AbstractProvider
{
    public function __construct()
    {
        $this->expressionLanguageVariables = [
            'country' => CountryService::getCountryByUri()
        ];

        $this->expressionLanguageProviders = [
            CountryConditionFunctionsProvider::class
        ];
    }
}
