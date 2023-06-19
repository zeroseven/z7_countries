<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Xclass;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Error\PageErrorHandler\PageErrorHandlerInterface;
use TYPO3\CMS\Core\Error\PageErrorHandler\PageErrorHandlerNotConfiguredException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Zeroseven\Countries\Context\CountryContext;
use Zeroseven\Countries\Service\CountryService;
use Zeroseven\Countries\Service\LanguageManipulationService;

class Site extends \TYPO3\CMS\Core\Site\Entity\Site
{
    public function __construct(string $identifier, int $rootPageId, array $configuration)
    {
        // Call the "original" Site
        parent::__construct($identifier, $rootPageId, $configuration);

        // Handle languages
        $originalLanguages = $this->languages;
        $manipulatedLanguages = LanguageManipulationService::getManipulatedLanguages($originalLanguages);

        // Sore languages in context
        $context = GeneralUtility::makeInstance(Context::class);
        $context->setAspect('country', GeneralUtility::makeInstance(CountryContext::class, $originalLanguages, $manipulatedLanguages));

        // Manipulate site
        if ($manipulatedLanguages !== null) {
            $this->languages = $manipulatedLanguages;
        }
    }

    public function getErrorHandler(int $statusCode): PageErrorHandlerInterface
    {
        if (($this->errorHandlers[$statusCode]['errorHandler'] ?? null) === self::ERRORHANDLER_TYPE_PAGE && ($country = CountryService::getCountryByUri()) && !$country->isEnabled()) {
            throw new PageErrorHandlerNotConfiguredException(sprintf('Configured error handling for "%s" is prevented because the page content is not available for the selected country.', self::ERRORHANDLER_TYPE_PAGE), 1663703635);
        }

        return parent::getErrorHandler($statusCode);
    }
}
