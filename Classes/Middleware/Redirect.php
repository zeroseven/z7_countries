<?php

declare(strict_types=1);

namespace Zeroseven\Countries\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Zeroseven\Countries\Service\CountryService;
use Zeroseven\Countries\Service\LanguageManipulationService;

class Redirect implements MiddlewareInterface
{
    /** @var ServerRequestInterface */
    protected $request;

    /** @var Site */
    protected $site;

    /** @var array|SiteLanguage */
    protected $languages = [];

    protected function init(ServerRequestInterface $request): void
    {
        $this->request = $request;
        $this->site = $request->getAttribute('site');
        $this->languages = $this->site->getLanguages();
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
        return (bool)($_COOKIE['disable-language-redirect'] ?? false);
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
        foreach ($this->languages as $language) {
            if ($language->getTwoLetterIsoCode() === $languageCode) {
                if ($countryCode && $countries = CountryService::getCountriesByLanguageUid($language->getLanguageId(), $this->site)) {
                    foreach ($countries as $country) {
                        if(strtolower($country->getIsoCode()) === $countryCode) {
                            return (string)LanguageManipulationService::getBase($language, $country);
                        }
                    }
                }

                return (string)LanguageManipulationService::getBase($language);
            }
        }

        return null;
    }

    protected function redirect(string $url): ResponseInterface
    {
        return (new RedirectResponse($url, 302))->withHeader('X-Redirect', 'z7_countries');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->init($request);

        // Go forwards ...
        if (!$this->isRootPage() || $this->isLocalReferer() || $this->isDisabled()) {
            return $handler->handle($request);
        }

        // Check browser language settings ...
        if ($languageSettings = $this->getAcceptedLanguages()) {
            foreach ($languageSettings as $value) {
                if ($url = $this->getRedirectUrl($value[0], $value[1])) {
                    return $this->redirect($url);
                }
            }
        }

        return $handler->handle($request);
    }
}
