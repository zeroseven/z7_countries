<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Hooks;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Aspect\PreviewAspect;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use Zeroseven\Countries\Service\CountryService;

class CountryPreview implements HookInterface
{
    public function check(array $parameters, TypoScriptFrontendController $typoScriptFrontendController): void
    {
        if (($country = CountryService::getCountryByUri()) && !$country->isEnabled()) {
            $context = GeneralUtility::makeInstance(Context::class);

            try {
                if ($context->getPropertyFromAspect('backend.user', 'isLoggedIn', false)) {
                    $context->setAspect('frontend.preview', GeneralUtility::makeInstance(PreviewAspect::class, true));
                } else {
                    $typoScriptFrontendController->pageNotFound = 1;
                }
            } catch (AspectNotFoundException $e) {
            }
        }
    }

    public static function register(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['determineId-PreProcessing'][self::class] = self::class . '->check';
    }
}
