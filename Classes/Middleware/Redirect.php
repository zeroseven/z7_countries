<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Middleware;

use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Zeroseven\Countries\Event\RedirectEvent;
use Zeroseven\Countries\Menu\LanguageMenu;

class Redirect implements MiddlewareInterface
{
    protected const REDIRECT_HEADER = 'X-z7country-redirect';

    private ServerRequestInterface $request;
    private RequestHandlerInterface $handler;
    private array $languageMenu;
    private EventDispatcherInterface $eventDispatcher;

    public function injectEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

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
        if (($refererHost = parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_HOST)) && $requestHost = $this->request->getUri()->getHost()) {
            return strtolower($refererHost) === strtolower($requestHost);
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
        /**
         * Returns something like this "de-DE,de;q=0.9,en-US;q=0.8,en;q=0."
         * as [['de', 'de'], ['de', null], ['en', 'us'], ['en', null]].
         */
        if ($httpAcceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null) {
            return array_filter(array_map(static function ($v) {
                return preg_match('/^(\w{2})(-(\w{2}))?($|;)/', $v, $matches) ? [$matches[1], $matches[3] ?? null] : null;
            }, GeneralUtility::trimExplode(',', strtolower($httpAcceptLanguage))));
        }

        return null;
    }

    protected function getRedirectUrl(): ?string
    {
        foreach ($this->getAcceptedLanguages() ?: [] as $value) {
            foreach ($this->languageMenu as $languageItem) {
                if ($languageItem->isAvailable() && $languageItem->getTwoLetterIsoCode() === $value[0]) {
                    if ($value[1]) {
                        foreach ($languageItem->getCountries() as $countryItem) {
                            if ($countryItem->isAvailable() && strtolower($countryItem->getIsoCode()) === $value[1]) {
                                return $countryItem->getLink();
                            }
                        }
                    }

                    return $languageItem->getLink();
                }
            }
        }

        return null;
    }

    protected function redirect(string $url, int $status): ResponseInterface
    {
        if ($url === (string)$this->request->getUri()) {
            return $this->handler->handle($this->request)->withHeader(self::REDIRECT_HEADER, 'same url');
        }

        return (new RedirectResponse($url, $status))->withHeader(self::REDIRECT_HEADER, 'true');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->init($request, $handler);

        if (
            $this->isRootPage()
            && !$this->isLocalReferer()
            && !$this->isDisabled()
            && !$this->isCrawler()
            && ($this->languageMenu = GeneralUtility::makeInstance(LanguageMenu::class)->render())
        ) {
            $url = $this->getRedirectUrl();
            $event = $this->eventDispatcher->dispatch(new RedirectEvent($this->languageMenu, $url));

            if ($url = $event->getUrl()) {
                return $this->redirect($url, $event->getStatus());
            }
        }

        return $handler->handle($request);
    }
}
