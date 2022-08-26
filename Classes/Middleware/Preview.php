<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Controller\ErrorPageController;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Aspect\PreviewAspect;
use TYPO3\CMS\Frontend\Page\PageAccessFailureReasons;
use Zeroseven\Countries\Service\CountryService;

class Preview implements MiddlewareInterface
{
    protected function createError(int $errorCode = 403): ResponseInterface
    {
        $content = GeneralUtility::makeInstance(ErrorPageController::class)->errorAction(
            GeneralUtility::makeInstance(PageAccessFailureReasons::class)->getMessageForReason(PageAccessFailureReasons::LANGUAGE_NOT_AVAILABLE),
            '',
            AbstractMessage::ERROR,
            0,
            $errorCode
        );

        return new HtmlResponse($content, $errorCode);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (($country = CountryService::getCountryByUri()) && !$country->isEnabled()) {
            $context = GeneralUtility::makeInstance(Context::class);

            try {
                if ($context->getPropertyFromAspect('backend.user', 'isLoggedIn', false)) {
                    $context->setAspect('frontend.preview', GeneralUtility::makeInstance(PreviewAspect::class, true));
                } else {
                    return $this->createError();
                }
            } catch (AspectNotFoundException $e) {
            }
        }

        return $handler->handle($request);
    }
}
