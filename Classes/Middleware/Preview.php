<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Error\Http\PageNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Aspect\PreviewAspect;
use TYPO3\CMS\Frontend\Controller\ErrorController;
use TYPO3\CMS\Frontend\Page\PageAccessFailureReasons;
use Zeroseven\Countries\Service\CountryService;

class Preview implements MiddlewareInterface
{

    /** @throws PageNotFoundException */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (($country = CountryService::getCountryByUri()) && !$country->isEnabled()) {
            $context = GeneralUtility::makeInstance(Context::class);

            try {
                if ($context->getPropertyFromAspect('backend.user', 'isLoggedIn', false)) {
                    $context->setAspect('frontend.preview', GeneralUtility::makeInstance(PreviewAspect::class, true));
                } else {
                    $reasonCode = PageAccessFailureReasons::LANGUAGE_NOT_AVAILABLE;
                    $message = GeneralUtility::makeInstance(PageAccessFailureReasons::class)->getMessageForReason($reasonCode);

                    return GeneralUtility::makeInstance(ErrorController::class)->pageNotFoundAction($request, $message, ['code' => $reasonCode])
                        ->withHeader('X-Extension', 'z7_countries')
                        ->withHeader('X-Country', $country->getIsoCode());
                }
            } catch (AspectNotFoundException $e) {
            }
        }

        return $handler->handle($request);
    }
}
