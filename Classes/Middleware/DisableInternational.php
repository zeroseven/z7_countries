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
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\ErrorController;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageAccessFailureReasons;
use Zeroseven\Countries\Service\CountryService;

class DisableInternational implements MiddlewareInterface
{
    /** @throws PageNotFoundException */
    protected function createErrorResponse(ServerRequestInterface $request): ResponseInterface
    {
        $reasonCode = PageAccessFailureReasons::LANGUAGE_NOT_AVAILABLE;
        $message = GeneralUtility::makeInstance(PageAccessFailureReasons::class)->getMessageForReason($reasonCode);

        return GeneralUtility::makeInstance(ErrorController::class)->pageNotFoundAction($request, $message, ['code' => $reasonCode])
            ->withHeader('X-Extension', 'z7_countries');
    }

    /** @throws AspectNotFoundException | SiteNotFoundException | PageNotFoundException */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return CountryService::getCountryByUri() === null
        && (($GLOBALS['TSFE'] ?? null) instanceof TypoScriptFrontendController)
        && ($uid = $GLOBALS['TSFE']->id)
        && ($languageUid = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('language', 'id'))
        && ($language = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($uid)->getLanguageById($languageUid))
        && ($language->toArray()['disable_international'] ?? false)
            ? $this->createErrorResponse($request)
            : $handler->handle($request);
    }
}
