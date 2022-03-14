<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Zeroseven\Countries\Utility\MenuUtility;

class Redirect implements MiddlewareInterface
{
    protected const REDIRECT_HEADER = 'X-Language-Redirect';

    protected function isRootPage(ServerRequestInterface $request): bool
    {
        return $request->getUri()->getPath() === '/';
    }

    protected function isLocalReferer(ServerRequestInterface $request): bool
    {
        if ($referer = $_SERVER['HTTP_REFERER'] ?? null) {
            return strtolower(parse_url($referer, PHP_URL_HOST)) === strtolower($request->getUri()->getHost());
        }

        return false;
    }

    protected function isDisabled(ServerRequestInterface $request): bool
    {
        return !empty($request->getHeader(self::REDIRECT_HEADER)) || ($_COOKIE['disable-language-redirect'] ?? false);
    }

    protected function getAcceptedLanguages(): ?array
    {
        if ($httpAcceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null) {
            return array_filter(array_map(static function ($v) {
                return preg_match('/^(\w{2})(-(\w{2}))?($|;)/', $v, $matches) ? [$matches[1], $matches[3] ?? null] : null;
            }, GeneralUtility::trimExplode(',', strtolower($httpAcceptLanguage))));
        }

        return null;
    }

    protected function getRedirectUrl(array $languageMenu, string $languageCode, string $countryCode = null): ?string
    {
        foreach ($languageMenu as $language) {
            if ($language['available'] && $language['object']->getTwoLetterIsoCode() === $languageCode) {
                if ($countryCode && ($language['countries'] ?? null)) {
                    foreach ($language['countries'] as $country) {
                        if ($country['available'] && strtolower($country['object']->getIsoCode()) === $countryCode) {
                            return $country['link'];
                        }
                    }
                }

                return $language['link'];
            }
        }

        return null;
    }

    protected function redirect(string $url): ResponseInterface
    {
        return (new RedirectResponse($url, 307))->withHeader(self::REDIRECT_HEADER, 'z7_countries');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (
            $this->isRootPage($request)
            && !$this->isLocalReferer($request)
            && !$this->isDisabled($request)
            && ($languageSettings = $this->getAcceptedLanguages())
            && ($languageMenu = GeneralUtility::makeInstance(MenuUtility::class)->getLanguageMenu())
        ) {
            foreach ($languageSettings as $value) {
                if ($url = $this->getRedirectUrl($languageMenu, $value[0], $value[1])) {
                    return $this->redirect($url);
                }
            }
        }

        return $handler->handle($request);
    }
}
