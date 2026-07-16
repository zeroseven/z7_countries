<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Xclass;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Error\PageErrorHandler\PageErrorHandlerInterface;
use TYPO3\CMS\Core\Error\PageErrorHandler\PageErrorHandlerNotConfiguredException;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\Entity\SiteSettings;
use TYPO3\CMS\Core\Site\Entity\SiteTSconfig;
use TYPO3\CMS\Core\Site\Entity\SiteTypoScript;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Zeroseven\Countries\Context\CountryContext;
use Zeroseven\Countries\Service\CountryService;
use Zeroseven\Countries\Service\LanguageManipulationService;

class Site extends \TYPO3\CMS\Core\Site\Entity\Site
{
    public function __construct(string $identifier, int $rootPageId, array $configuration, ?SiteSettings $settings = null, ?SiteTypoScript $typoscript = null, ?SiteTSconfig $tsConfig = null)
    {
        // Call the "original" Site
        parent::__construct($identifier, $rootPageId, $configuration, $settings, $typoscript, $tsConfig);

        // Sites without country configuration must not touch the country
        // context: all sites of an installation run through this constructor,
        // and the last one would otherwise overwrite the aspect.
        if (!$this->hasCountryConfiguration($configuration)) {
            return;
        }

        // Handle languages
        $originalLanguages = $this->languages;
        $manipulatedLanguages = LanguageManipulationService::getManipulatedLanguages($originalLanguages);

        // Store languages in context
        $context = GeneralUtility::makeInstance(Context::class);
        $context->setAspect('country', GeneralUtility::makeInstance(CountryContext::class, $originalLanguages, $manipulatedLanguages));

        // Manipulate site
        if ($manipulatedLanguages !== null) {
            $this->languages = $manipulatedLanguages;
        }
    }

    protected function hasCountryConfiguration(array $configuration): bool
    {
        foreach ($configuration['languages'] ?? [] as $languageConfiguration) {
            if (!empty($languageConfiguration['countries'])) {
                return true;
            }
        }

        return false;
    }

    public function getErrorHandler(int $statusCode): PageErrorHandlerInterface
    {
        if (($this->errorHandlers[$statusCode]['errorHandler'] ?? null) === self::ERRORHANDLER_TYPE_PAGE) {
            $country = CountryService::getCountryByUri();

            if ($country && !$country->isEnabled()) {
                throw new PageErrorHandlerNotConfiguredException(sprintf('Configured error handling for "%s" is prevented because the page content is not available for the selected country.', self::ERRORHANDLER_TYPE_PAGE), 1663703635);
            }

            // The error page cannot be rendered in the international variant of
            // a language with "disable_international" - it is blocked as well.
            $language = ($request = $GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface ? $request->getAttribute('language') : null;
            if ($country === null && $language instanceof SiteLanguage && ($language->toArray()['disable_international'] ?? false)) {
                throw new PageErrorHandlerNotConfiguredException(sprintf('Configured error handling for "%s" is prevented because the page content is not available for international visitors.', self::ERRORHANDLER_TYPE_PAGE), 1663703636);
            }
        }

        return parent::getErrorHandler($statusCode);
    }
}
