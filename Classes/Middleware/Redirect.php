<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Middleware;

use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Zeroseven\Countries\Menu\LanguageMenu;

class Redirect implements MiddlewareInterface
{
    protected const REDIRECT_HEADER = 'X-z7country-redirect';

    private ServerRequestInterface $request;

    private RequestHandlerInterface $handler;

    private array $languageMenu;

    protected function init(ServerRequestInterface $request, RequestHandlerInterface $handler): void
    {
        $this->request = $request;
        $this->handler = $handler;
    }

    protected function isRootPage(): bool
    {
        return $this->request->getUri()->getPath() === '/';
    }

    protected function isLocalReferer(): bool
    {
        if ($referer = $_SERVER['HTTP_REFERER'] ?? null) {
            return strtolower(parse_url($referer, PHP_URL_HOST)) === strtolower($this->request->getUri()->getHost());
        }

        return false;
    }

    protected function isDisabled(): bool
    {
        return !empty($this->request->getHeader(self::REDIRECT_HEADER)) || ($_COOKIE['disable-language-redirect'] ?? false);
    }

    protected function isCrawler(): bool
    {
        return class_exists(CrawlerDetect::class) && method_exists(CrawlerDetect::class, 'isCrawler') && GeneralUtility::makeInstance(CrawlerDetect::class)->isCrawler();
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

    protected function getRedirectUrl(string $languageCode, string $countryCode = null): ?string
    {
        foreach ($this->languageMenu as $languageItem) {
            if ($languageItem->isAvailable() && $languageItem->getTwoLetterIsoCode() === $languageCode) {
                if ($countryCode) {
                    foreach ($languageItem->getCountries() as $countryItem) {
                        if ($countryItem->isAvailable() && strtolower($countryItem->getIsoCode()) === $countryCode) {
                            return $countryItem->getLink();
                        }
                    }
                }

                return $languageItem->getLink();
            }
        }

        return null;
    }

    protected function redirect(string $url): ResponseInterface
    {
        if ($url === (string)$this->request->getUri()) {
            return $this->handler->handle($this->request)->withHeader(self::REDIRECT_HEADER, 'same url');
        }

        return (new RedirectResponse($url, 307))->withHeader(self::REDIRECT_HEADER, 'true');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->init($request, $handler);

        if (
            $this->isRootPage()
            && !$this->isLocalReferer()
            && !$this->isDisabled()
            && !$this->isCrawler()
            && ($languageSettings = $this->getAcceptedLanguages())
            && ($this->languageMenu = GeneralUtility::makeInstance(LanguageMenu::class)->render())
        ) {
            foreach ($languageSettings as $value) {
                if ($url = $this->getRedirectUrl($value[0], $value[1])) {
                    return $this->redirect($url);
                }
            }
        }

        return $handler->handle($request);
    }
}
